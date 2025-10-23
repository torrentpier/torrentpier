/**
 * Karma System JavaScript
 *
 * @package TorrentPier\Mod\KarmaSystem
 * @author TorrentPier Team
 * @license MIT
 */

(function() {
    'use strict';

    /**
     * Karma voting functionality
     */
    class KarmaVoting {
        constructor() {
            this.init();
        }

        /**
         * Initialize event listeners
         */
        init() {
            document.addEventListener('DOMContentLoaded', () => {
                this.attachVoteListeners();
            });
        }

        /**
         * Attach click listeners to vote buttons
         */
        attachVoteListeners() {
            const voteButtons = document.querySelectorAll('.karma-vote-btn');

            voteButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.handleVote(button);
                });
            });
        }

        /**
         * Handle vote button click
         *
         * @param {HTMLElement} button The clicked button
         */
        handleVote(button) {
            // Prevent double-clicking
            if (button.classList.contains('loading')) {
                return;
            }

            const userId = parseInt(button.dataset.userId);
            const voteValue = parseInt(button.dataset.vote);

            // Validate
            if (!userId || !voteValue) {
                this.showToast('Invalid vote parameters', 'error');
                return;
            }

            // Show loading state
            button.classList.add('loading');
            button.disabled = true;

            // Send AJAX request
            this.sendVote(userId, voteValue)
                .then(response => {
                    this.handleVoteSuccess(button, response);
                })
                .catch(error => {
                    this.handleVoteError(button, error);
                })
                .finally(() => {
                    button.classList.remove('loading');
                    button.disabled = false;
                });
        }

        /**
         * Send vote to server
         *
         * @param {number} userId Target user ID
         * @param {number} voteValue Vote value (1 or -1)
         * @returns {Promise}
         */
        sendVote(userId, voteValue) {
            return new Promise((resolve, reject) => {
                ajax_request('karma_vote', {
                    user_id: userId,
                    vote: voteValue
                }, function(response) {
                    if (response.success) {
                        resolve(response);
                    } else {
                        reject(response.message || 'Vote failed');
                    }
                }, function(error) {
                    reject(error);
                });
            });
        }

        /**
         * Handle successful vote
         *
         * @param {HTMLElement} button The vote button
         * @param {Object} response Server response
         */
        handleVoteSuccess(button, response) {
            // Get button container
            const container = button.closest('.karma-buttons');
            const allButtons = container.querySelectorAll('.karma-vote-btn');

            // Remove active class from all buttons
            allButtons.forEach(btn => btn.classList.remove('active'));

            // Add active class to clicked button
            button.classList.add('active');

            // Show success message
            this.showToast(response.message || 'Vote recorded', 'success');

            // Update karma display if present
            this.updateKarmaDisplay(button.dataset.userId, response.karma);
        }

        /**
         * Handle vote error
         *
         * @param {HTMLElement} button The vote button
         * @param {string} error Error message
         */
        handleVoteError(button, error) {
            this.showToast(error || 'Failed to record vote', 'error');
        }

        /**
         * Update karma display
         *
         * @param {number} userId User ID
         * @param {number} karma New karma value
         */
        updateKarmaDisplay(userId, karma) {
            // Update karma badge if present
            const badges = document.querySelectorAll(`.karma-badge[data-user-id="${userId}"]`);
            badges.forEach(badge => {
                badge.textContent = karma;
                badge.classList.remove('positive', 'negative');
                badge.classList.add(karma >= 0 ? 'positive' : 'negative');
            });

            // Update profile karma if present
            const profileKarma = document.querySelector('.profile-karma .karma-points');
            if (profileKarma) {
                profileKarma.textContent = karma;
            }
        }

        /**
         * Show toast notification
         *
         * @param {string} message Message to display
         * @param {string} type Toast type (success, error)
         */
        showToast(message, type = 'success') {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `karma-toast ${type}`;
            toast.textContent = message;

            // Add to page
            document.body.appendChild(toast);

            // Remove after 3 seconds
            setTimeout(() => {
                toast.style.animation = 'karma-slide-in 0.3s ease reverse';
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }

        /**
         * Load karma statistics
         *
         * @param {number} userId User ID
         * @returns {Promise}
         */
        loadStats(userId) {
            return new Promise((resolve, reject) => {
                ajax_request('karma_stats', {
                    user_id: userId
                }, function(response) {
                    if (response.success) {
                        resolve(response.stats);
                    } else {
                        reject(response.message || 'Failed to load stats');
                    }
                }, function(error) {
                    reject(error);
                });
            });
        }
    }

    /**
     * Initialize karma voting when DOM is ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.KarmaVoting = new KarmaVoting();
        });
    } else {
        window.KarmaVoting = new KarmaVoting();
    }

})();
