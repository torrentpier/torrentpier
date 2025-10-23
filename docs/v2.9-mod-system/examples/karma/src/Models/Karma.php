<?php
/**
 * Karma Model
 *
 * @package TorrentPier\Mod\KarmaSystem\Models
 * @author TorrentPier Team
 * @license MIT
 */

namespace TorrentPier\Mod\KarmaSystem\Models;

/**
 * Karma data model
 *
 * Handles all database operations related to karma
 */
class Karma
{
    /**
     * Get user's karma points
     *
     * @param int $user_id User ID
     * @return int|null Karma points or null if not found
     */
    public static function getKarma($user_id)
    {
        $result = DB()->fetchField("
            SELECT karma_points FROM bb_karma WHERE user_id = ?
        ", $user_id);

        return $result !== false ? (int) $result : null;
    }

    /**
     * Get detailed karma information
     *
     * @param int $user_id User ID
     * @return array|null Karma details or null if not found
     */
    public static function getDetails($user_id)
    {
        return DB()->fetch_row("
            SELECT
                user_id,
                karma_points,
                positive_votes,
                negative_votes,
                last_updated
            FROM bb_karma
            WHERE user_id = ?
        ", $user_id);
    }

    /**
     * Get user's vote for another user
     *
     * @param int $voter_id Voter user ID
     * @param int $target_user_id Target user ID
     * @return int|null Vote value (1, -1) or null if no vote
     */
    public static function getUserVote($voter_id, $target_user_id)
    {
        $result = DB()->fetchField("
            SELECT value FROM bb_karma_votes
            WHERE voter_id = ? AND user_id = ?
        ", $voter_id, $target_user_id);

        return $result !== false ? (int) $result : null;
    }

    /**
     * Cast a vote
     *
     * @param int $voter_id Voter user ID
     * @param int $target_user_id Target user ID
     * @param int $vote_value Vote value (1 or -1)
     * @param string $reason Optional reason
     * @return bool Success
     * @throws \Exception On error
     */
    public static function vote($voter_id, $target_user_id, $vote_value, $reason = '')
    {
        DB()->beginTransaction();

        try {
            // Check if vote already exists
            $existing_vote = self::getUserVote($voter_id, $target_user_id);

            if ($existing_vote !== null) {
                // Update existing vote
                DB()->query("
                    UPDATE bb_karma_votes
                    SET value = ?, created_at = ?, reason = ?
                    WHERE voter_id = ? AND user_id = ?
                ", $vote_value, time(), $reason, $voter_id, $target_user_id);
            } else {
                // Insert new vote
                DB()->query("
                    INSERT INTO bb_karma_votes (user_id, voter_id, value, created_at, reason)
                    VALUES (?, ?, ?, ?, ?)
                ", $target_user_id, $voter_id, $vote_value, time(), $reason);
            }

            // Recalculate target user's karma
            self::recalculate($target_user_id);

            DB()->commit();
            return true;
        } catch (\Exception $e) {
            DB()->rollBack();
            throw $e;
        }
    }

    /**
     * Recalculate karma for a user
     *
     * @param int $user_id User ID
     * @return void
     */
    public static function recalculate($user_id)
    {
        // Calculate total karma and vote counts
        $stats = DB()->fetch_row("
            SELECT
                COALESCE(SUM(value), 0) as total_karma,
                COALESCE(SUM(CASE WHEN value > 0 THEN 1 ELSE 0 END), 0) as positive_votes,
                COALESCE(SUM(CASE WHEN value < 0 THEN 1 ELSE 0 END), 0) as negative_votes
            FROM bb_karma_votes
            WHERE user_id = ?
        ", $user_id);

        $karma_points = (int) $stats['total_karma'];
        $positive_votes = (int) $stats['positive_votes'];
        $negative_votes = (int) $stats['negative_votes'];

        // Update or insert karma record
        DB()->query("
            INSERT INTO bb_karma (user_id, karma_points, positive_votes, negative_votes, last_updated)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                karma_points = VALUES(karma_points),
                positive_votes = VALUES(positive_votes),
                negative_votes = VALUES(negative_votes),
                last_updated = VALUES(last_updated)
        ", $user_id, $karma_points, $positive_votes, $negative_votes, time());
    }

    /**
     * Recalculate karma for all users
     *
     * @return int Number of users updated
     */
    public static function recalculateAll()
    {
        // Get all users who have received votes
        $users = DB()->fetch_rowset("
            SELECT DISTINCT user_id FROM bb_karma_votes
        ");

        $count = 0;
        foreach ($users as $user) {
            self::recalculate($user['user_id']);
            $count++;
        }

        return $count;
    }

    /**
     * Get top users by karma
     *
     * @param int $limit Number of users to return
     * @return array Array of user data with karma
     */
    public static function getTopUsers($limit = 10)
    {
        return DB()->fetch_rowset("
            SELECT
                k.user_id,
                k.karma_points,
                k.positive_votes,
                k.negative_votes,
                u.username
            FROM bb_karma k
            INNER JOIN bb_users u ON k.user_id = u.user_id
            ORDER BY k.karma_points DESC
            LIMIT ?
        ", $limit);
    }

    /**
     * Get user's recent votes (given by user)
     *
     * @param int $voter_id Voter user ID
     * @param int $limit Number of votes to return
     * @return array Array of vote records
     */
    public static function getUserVotesGiven($voter_id, $limit = 10)
    {
        return DB()->fetch_rowset("
            SELECT
                v.*,
                u.username as target_username
            FROM bb_karma_votes v
            INNER JOIN bb_users u ON v.user_id = u.user_id
            WHERE v.voter_id = ?
            ORDER BY v.created_at DESC
            LIMIT ?
        ", $voter_id, $limit);
    }

    /**
     * Get votes received by user
     *
     * @param int $user_id User ID
     * @param int $limit Number of votes to return
     * @return array Array of vote records
     */
    public static function getUserVotesReceived($user_id, $limit = 10)
    {
        return DB()->fetch_rowset("
            SELECT
                v.*,
                u.username as voter_username
            FROM bb_karma_votes v
            INNER JOIN bb_users u ON v.voter_id = u.user_id
            WHERE v.user_id = ?
            ORDER BY v.created_at DESC
            LIMIT ?
        ", $user_id, $limit);
    }

    /**
     * Delete all votes for a user
     *
     * @param int $user_id User ID
     * @return void
     */
    public static function deleteUserVotes($user_id)
    {
        DB()->beginTransaction();

        try {
            // Delete votes given by user
            DB()->query("DELETE FROM bb_karma_votes WHERE voter_id = ?", $user_id);

            // Delete votes received by user
            DB()->query("DELETE FROM bb_karma_votes WHERE user_id = ?", $user_id);

            // Delete karma record
            DB()->query("DELETE FROM bb_karma WHERE user_id = ?", $user_id);

            DB()->commit();
        } catch (\Exception $e) {
            DB()->rollBack();
            throw $e;
        }
    }
}
