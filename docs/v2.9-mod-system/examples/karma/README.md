# Karma System Mod for TorrentPier v2.9

A comprehensive user reputation system with upvote/downvote functionality for TorrentPier v2.9+.

![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)
![TorrentPier](https://img.shields.io/badge/torrentpier-2.9%2B-orange.svg)

## Features

- ✅ **User Karma System** - Track user reputation with positive and negative votes
- ✅ **Voting Controls** - Configurable minimum posts, daily vote limits, and vote values
- ✅ **Flexible Display** - Show karma in profiles, posts, and usernames
- ✅ **Admin Panel** - Full web-based configuration interface
- ✅ **Multilingual** - Includes English and Russian translations
- ✅ **AJAX Voting** - Smooth voting experience without page reloads
- ✅ **Vote History** - Track and audit all karma changes
- ✅ **Cron Integration** - Automatic daily karma recalculation
- ✅ **Responsive Design** - Mobile-friendly interface

## Requirements

- **TorrentPier:** >= 3.0.0
- **PHP:** >= 8.2.0
- **MySQL:** >= 5.7 or MariaDB >= 10.2

## Installation

### Via Web Interface (Recommended)

1. Download the latest release
2. Go to **Admin Panel → Mods**
3. Click **"Upload Mod"**
4. Select the downloaded `.zip` file
5. Click **"Install"**

### Via Command Line

```bash
# Download/copy mod to mods directory
cd /path/to/torrentpier
cp -r karma-system mods/

# Install mod
php mod.php install karma-system

# Verify installation
php mod.php list --active
```

## Configuration

### Web Interface

1. Go to **Admin Panel → Mods → Karma Settings**
2. Configure options:
   - **Enabled**: Turn karma system on/off
   - **Min Posts**: Minimum posts required to vote (default: 10)
   - **Votes Per Day**: Maximum votes per user per day (default: 5)
   - **Upvote Value**: Points for positive vote (default: 1)
   - **Downvote Value**: Points for negative vote (default: -1)
   - **Display Options**: Where to show karma (profile, posts, username)
   - **Voting Rules**: Require reason, allow self-voting, etc.
3. Click **"Save Settings"**

### Configuration File

Edit `/mods/karma-system/config.php` for advanced configuration:

```php
return [
    'karma' => [
        'enabled' => true,
        'min_posts_to_vote' => 10,
        'votes_per_day' => 5,
        'upvote_value' => 1,
        'downvote_value' => -1,
        'show_in_profile' => true,
        'show_in_posts' => true,
        'show_in_username' => false,
        'require_reason' => false,
        'self_vote_allowed' => false,
        'icon_upvote' => '👍',
        'icon_downvote' => '👎',
    ]
];
```

## Usage

### For Users

**Voting in Posts:**
- Click 👍 button to upvote (increase karma)
- Click 👎 button to downvote (decrease karma)
- Your vote is recorded immediately
- You can change your vote at any time

**Viewing Karma:**
- View your karma in your profile
- See other users' karma in their profiles
- Karma may appear next to usernames (if enabled)

**Voting Limits:**
- You must have at least 10 posts to vote (configurable)
- You can cast 5 votes per day (configurable)
- You cannot vote for yourself (unless admin allows)

### For Administrators

**Admin Panel:**
- Access settings at **Admin → Mods → Karma Settings**
- View karma statistics and leaderboard
- Manually adjust user karma if needed
- View vote history and audit logs

**Command Line:**
```bash
# Recalculate all karma
php mod.php run karma-system recalculateAllKarma

# View mod status
php mod.php info karma-system

# Update mod
php mod.php update karma-system
```

## Database Schema

The mod creates three tables:

### `bb_karma`
Stores user karma points and statistics.

| Column | Type | Description |
|--------|------|-------------|
| user_id | INT | User ID (primary key) |
| karma_points | INT | Total karma points |
| positive_votes | INT | Number of upvotes received |
| negative_votes | INT | Number of downvotes received |
| last_updated | INT | Last update timestamp |

### `bb_karma_votes`
Stores individual vote records.

| Column | Type | Description |
|--------|------|-------------|
| vote_id | INT | Vote ID (auto increment) |
| user_id | INT | User who received the vote |
| voter_id | INT | User who cast the vote |
| value | TINYINT | Vote value (1 or -1) |
| created_at | INT | Vote timestamp |
| reason | TEXT | Optional reason for vote |

### `bb_karma_history`
Optional audit log for karma changes.

| Column | Type | Description |
|--------|------|-------------|
| history_id | INT | History ID (auto increment) |
| user_id | INT | User whose karma changed |
| karma_before | INT | Karma before change |
| karma_after | INT | Karma after change |
| change_type | VARCHAR(32) | Type of change |
| changed_by | INT | User who made the change |
| created_at | INT | Change timestamp |
| notes | TEXT | Optional notes |

## Hooks

This mod registers the following hooks:

### Filters
- `user.display_name` - Adds karma to username display
- `profile.info_display` - Shows karma in user profile

### Actions
- `post.after_display` - Displays voting buttons in posts
- `ajax.karma_vote` - Handles vote AJAX requests
- `ajax.karma_stats` - Returns karma statistics
- `admin.page.karma-settings` - Admin settings page
- `admin.save.karma-settings` - Saves admin settings
- `template.head` - Adds CSS and JavaScript assets

## API

### PHP API

```php
use TorrentPier\Mod\KarmaSystem\Models\Karma;

// Get user's karma
$karma = Karma::getKarma($user_id);

// Get detailed karma info
$details = Karma::getDetails($user_id);

// Cast a vote
Karma::vote($voter_id, $target_user_id, 1); // Upvote
Karma::vote($voter_id, $target_user_id, -1); // Downvote

// Get top users
$top_users = Karma::getTopUsers(10);

// Recalculate karma
Karma::recalculate($user_id);
Karma::recalculateAll();
```

### JavaScript API

```javascript
// Send vote via AJAX
ajax_request('karma_vote', {
    user_id: 123,
    vote: 1 // 1 = upvote, -1 = downvote
}, function(response) {
    console.log('New karma:', response.karma);
});

// Get karma statistics
ajax_request('karma_stats', {
    user_id: 123
}, function(response) {
    console.log('Stats:', response.stats);
});
```

## Customization

### Custom Templates

Override templates by creating files in `/styles/templates/mods/karma-system/`:

- `post_karma.tpl` - Voting buttons in posts
- `profile_karma.tpl` - Karma display in profile

### Custom Styles

Override styles by creating `/styles/css/mods/karma-system.css`:

```css
.karma-vote-btn {
    /* Your custom styles */
}
```

### Custom Icons

Change voting icons in admin settings or config:

```php
'icon_upvote' => '⬆️',
'icon_downvote' => '⬇️',
```

## Troubleshooting

### Votes not working

**Check:**
1. User has enough posts to vote (`min_posts_to_vote` setting)
2. User hasn't exceeded daily limit (`votes_per_day` setting)
3. User isn't voting for themselves (unless `self_vote_allowed` is true)
4. JavaScript console for errors

### Karma not displaying

**Check:**
1. Karma system is enabled in settings
2. Display options are enabled (`show_in_profile`, `show_in_posts`)
3. Clear cache: `php mod.php cache:clear karma-system`

### Database errors

**Solution:**
```bash
# Reinstall database tables
php mod.php uninstall karma-system --keep-data
php mod.php install karma-system
```

## Changelog

### Version 2.0.0 (2025-01-15)
- ✨ Complete rewrite for TorrentPier v2.9 mod system
- ✨ Hook-based architecture (no core modifications)
- ✨ Admin web interface
- ✨ AJAX voting
- ✨ Vote history and audit logging
- ✨ Automatic daily recalculation
- ✨ Improved performance with caching
- ✨ Mobile-responsive design

### Version 1.x
- Legacy version for TorrentPier 2.8 and earlier
- Required manual file modifications
- Limited functionality

## Migrating from v1.x

If you're upgrading from the old karma mod (v1.x):

1. **Backup your database** - especially `bb_karma` and `bb_karma_votes` tables
2. **Uninstall old mod** - remove all manual file modifications
3. **Install v2.0** - using the new installation method
4. **Run migration** - `php mod.php migrate karma-system`
5. **Recalculate karma** - `php mod.php run karma-system recalculateAllKarma`
6. **Test thoroughly** - verify all features work correctly

Your existing karma data will be preserved.

## Support

### Documentation
- [Mod Development Guide](../../MOD-DEVELOPMENT.md)
- [Migration Guide](../../MIGRATION-GUIDE.md)
- [API Reference](../../API-REFERENCE.md)

### Getting Help
- **Forum**: https://torrentpier.com/forum/mods
- **GitHub Issues**: https://github.com/torrentpier/torrentpier/issues
- **Discord**: #mod-development channel

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This mod is open-source software licensed under the [MIT License](LICENSE).

## Credits

**Author:** TorrentPier Team
**Maintainer:** TorrentPier Community
**Contributors:** See [GitHub contributors](https://github.com/torrentpier/torrentpier/graphs/contributors)

## Acknowledgments

- Original karma mod concept from phpBB MODs community
- Icons: [Twemoji](https://twemoji.twitter.com/) by Twitter
- Inspiration from Reddit and Stack Overflow reputation systems

---

**Made with ❤️ for TorrentPier community**
