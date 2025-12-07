[![TorrentPier](https://raw.githubusercontent.com/torrentpier/.github/refs/heads/main/versions/Cattle.png)](https://github.com/torrentpier)

# 📖 Change Log

## [nightly](https://nightly.link/torrentpier/torrentpier/workflows/ci/master/TorrentPier-master)

### 🚀 Features

- Add up/down migration for topic attachment columns - ([94ce3f0](https://github.com/torrentpier/torrentpier/commit/94ce3f0e141db7c686a4c1f8a73f4a0c66f28cc1))
- Remove legacy `attach_mod` tables and improve migration script - ([ec2fb19](https://github.com/torrentpier/torrentpier/commit/ec2fb19a749d8041b4000a10b8874b05a5f8fff5))
- Add migration script for torrent attachments to topic-based storage - ([c16545e](https://github.com/torrentpier/torrentpier/commit/c16545e45b1409da5da45296d8fc69368d938677))
- Introduce `Topic\Guard` class for user authorization checks - ([d7c84e2](https://github.com/torrentpier/torrentpier/commit/d7c84e2fbffa40f1b3efaa031474e41e10bf400d))
- Enhance tracker functionality with `tracker_status` support and attachment improvements - ([244cf19](https://github.com/torrentpier/torrentpier/commit/244cf19b0f827a58b14e2d0f4b858abd8b035222))
- Add `fetchPairs` method and `autoRegister` logic to streamline torrent registration - ([83f2678](https://github.com/torrentpier/torrentpier/commit/83f26786469e6b411669614b008ac96283550620))
- Enable attachment upload and deletion for the first post in tracker forums - ([8e1e6c6](https://github.com/torrentpier/torrentpier/commit/8e1e6c6eeeb93c0de01c5b461bea3f53b17c9339))

### 🐛 Bug Fixes

- *(ip)* Add universal IP decode method for mixed format handling ([#2241](https://github.com/torrentpier/torrentpier/pull/2241)) - ([094926f](https://github.com/torrentpier/torrentpier/commit/094926fec072aa1aef61c1650cc5624cdc533acc))
- Add exception handling to `Registry::unregister` to ensure cron job continuity - ([cbf980a](https://github.com/torrentpier/torrentpier/commit/cbf980ada8e1b44bc2473be2000d27b96cfdff1a))
- Ensure null-safe check for `$bt_userdata` in ratio handling & improve attachment migration tracking - ([6054770](https://github.com/torrentpier/torrentpier/commit/60547701df245b31c49a754af2fef7876df9d05e))
- Handle potential null values in cron run times and intervals - ([ffac8ae](https://github.com/torrentpier/torrentpier/commit/ffac8ae0beb696d75d849ee091bc1a08bc00e3dd))

### 📦 Dependencies

- *(deps)* Bump twig/twig from 3.22.0 to 3.22.1 ([#2234](https://github.com/torrentpier/torrentpier/pull/2234)) - ([11c6cb7](https://github.com/torrentpier/torrentpier/commit/11c6cb78c579bbb74eb215d6be9692d00e5a7725))
- *(deps)* Bump nesbot/carbon from 3.10.3 to 3.11.0 ([#2236](https://github.com/torrentpier/torrentpier/pull/2236)) - ([a5645a2](https://github.com/torrentpier/torrentpier/commit/a5645a245b86822f9b14838a900105b29ed44dd0))

### 💼 Other

- *(admin)* Remove legacy attachment-related admin files** - ([bf177b7](https://github.com/torrentpier/torrentpier/commit/bf177b7576132d9095f387fb004f1069ecd5fe16))
- *(attachments)* Remove legacy attachment-mod components and maintenance cron** - ([0ea2a4a](https://github.com/torrentpier/torrentpier/commit/0ea2a4a3668ec216797e7c3c4f2d26cd138ce352))
- *(tracker)* Remove legacy attachment dependency and update queries** - ([0f572d6](https://github.com/torrentpier/torrentpier/commit/0f572d638a164e0212cca7c9eb5aee8f84cf3f52))
- Update minimum PHP version to 8.4 across workflows, dependencies, and documentation ([#2239](https://github.com/torrentpier/torrentpier/pull/2239)) - ([b49cd41](https://github.com/torrentpier/torrentpier/commit/b49cd41789f16a39792da790d0247a8334b2fe49))
- Enhance TorrServer integration and streamline validation logic** - ([55391f5](https://github.com/torrentpier/torrentpier/commit/55391f5beea4d9bc9e16e5cad0ea8d7b57074c06))
- Enhance language variable handling and streamline template logic** - ([045660f](https://github.com/torrentpier/torrentpier/commit/045660f1cdd51e5a83f4af23357fc5d98d0836f0))
- Remove legacy attachment deletion logic and optimize M3U handling** - ([e7a6359](https://github.com/torrentpier/torrentpier/commit/e7a6359055b433fc3b4ab000f64d5c400e474dbb))
- Clean up attachment and torrent logic** - ([b0e57a6](https://github.com/torrentpier/torrentpier/commit/b0e57a69e67cbaae364423cef5e9b7cce429c8eb))
- Improve attachment handling and migrate download tracker logic** - ([6a7482d](https://github.com/torrentpier/torrentpier/commit/6a7482d3f596cbaa4ed61108a60413823beeb837))
- Split `Torrent` class into modular components under `TorrentPier\Torrent` namespace** - ([265f01c](https://github.com/torrentpier/torrentpier/commit/265f01cdb9301a594f7b4845026604e163bacc6a))
- Migrate `TorrentFileList` to `Torrent\FileList` with namespace update** - ([e519d1c](https://github.com/torrentpier/torrentpier/commit/e519d1ccb4b365b94f02bafbe9f5f7e2a6998897))
- Add download tracking system with daily limits and aggregation** - ([797d06a](https://github.com/torrentpier/torrentpier/commit/797d06ae82e0d8398c156b071b6f3faef2aec558))
- Update `dl_url` format and improve attachment path handling** - ([712d59d](https://github.com/torrentpier/torrentpier/commit/712d59d031cfc2299cab195587ba0cfc95fa6003))
- Replace `attach_id` with `topic_id` across torrent logic and templates** - ([e098ac3](https://github.com/torrentpier/torrentpier/commit/e098ac3f8b6415cc42dab1d800114d87910843be))
- Update torrent template variables and remove legacy logic checks** - ([2daa494](https://github.com/torrentpier/torrentpier/commit/2daa4945fabce9b8a4fee60086e24d12766915df))
- Remove `attach_id` from `bb_bt_torrents` and simplify torrent logic** - ([3ee3bcf](https://github.com/torrentpier/torrentpier/commit/3ee3bcf3139de7b81a14adf8835e3de12f67dc0d))
- Add `tracker_status` column and update torrent methods** - ([a6585d6](https://github.com/torrentpier/torrentpier/commit/a6585d68944f3d3aee4205b20c336cd4b4af7696))
- Replace `attach_id` with `topic_id` in torrents logic** - ([89aead1](https://github.com/torrentpier/torrentpier/commit/89aead1dec4b2d4b328c3121657da8538d654694))
- Remove deprecated attachment-related columns and indexes** - ([d03209e](https://github.com/torrentpier/torrentpier/commit/d03209e29327ab1b2e620cd634253408fb9db175))
- Update caching headers and remove unused attachment constants** - ([d55bff4](https://github.com/torrentpier/torrentpier/commit/d55bff49c338bbfc8288fbbb90d924fb02d8a61d))
- Remove unused `orig_name` and `file_path` properties from `Upload` class** - ([2f464d3](https://github.com/torrentpier/torrentpier/commit/2f464d3a7a326da8e50918b0d5ee2e04e356179a))
- Remove `TorrentUpload` class and legacy torrent handling logic** - ([3332649](https://github.com/torrentpier/torrentpier/commit/33326495db3b0f10439fea97fc5ab9d2002dfa96))
- Standardize attachment logic and fix error handling** - ([66e7233](https://github.com/torrentpier/torrentpier/commit/66e72336b73f218df248d9f119e56fba12d62653))
- Remove `attach_id` references and standardize on `topic_id`** - ([a1ca111](https://github.com/torrentpier/torrentpier/commit/a1ca1114168c326a6fcca33905c017dc93bfdfdb))
- Rename `Attach.php` and move to root for better organization** - ([ac65ae0](https://github.com/torrentpier/torrentpier/commit/ac65ae0c4e819f580349ee3b34a7ff467f747c58))
- Remove legacy attachment mod and associated functions** - ([310e49a](https://github.com/torrentpier/torrentpier/commit/310e49ad4e5d4647b49f28b884d54cdbdff903ac))
- Remove legacy thumbnail handling from attachments** - ([13176b3](https://github.com/torrentpier/torrentpier/commit/13176b30175819f570e9fcb760c8d1e0b52f0ac1))
- Drop `bb_extensions` and `bb_extension_groups` tables** - ([d52691d](https://github.com/torrentpier/torrentpier/commit/d52691ddfd79a6f4a17f7c5092ef9e9695cfefb0))
- Remove `attach_extensions` support and related constants** - ([654a076](https://github.com/torrentpier/torrentpier/commit/654a076c206e0869e1c14b6b0b60d2a92a2d9c88))
- Remove thumbnail support from attachment handling** - ([b3b4e33](https://github.com/torrentpier/torrentpier/commit/b3b4e3367137428cbe998109769a32561f8a0706))
- Update `bb_setcookie` to use nullable lifetime parameter** - ([30a41fc](https://github.com/torrentpier/torrentpier/commit/30a41fc860b8b340b01908e1986565068bb349c5))
- Remove legacy attachment mod and related functionality** - ([36f8cb2](https://github.com/torrentpier/torrentpier/commit/36f8cb25dcfbc33983357e3948912c3d139809cc))
- Replace `attach_id` references with `topic_id` in torrent view logic** - ([19a48a0](https://github.com/torrentpier/torrentpier/commit/19a48a0884dd6def8e29dfa787124f2385476cb2))
- Streamline attachment logic and replace `attach_id` references with `topic_id`** - ([c208b50](https://github.com/torrentpier/torrentpier/commit/c208b50e498048c661d1e9c3de3040fe5afe874b))
- Implement `TorrentUpload` class for managing torrent file attachments** - ([d65a11a](https://github.com/torrentpier/torrentpier/commit/d65a11ad289eaff3eb75c9a9386e8ee919316fdd))
- Remove obsolete attachment logic from common module** - ([ec9894f](https://github.com/torrentpier/torrentpier/commit/ec9894feb52d463478a166cc15859f5a9ce3a94a))
- Add new columns for attachment metadata in topics table** - ([be6d83f](https://github.com/torrentpier/torrentpier/commit/be6d83f1aec95036947d780b0563463ee00efa14))
- Remove `attach_id` logic from torrent maintenance cron** - ([6656310](https://github.com/torrentpier/torrentpier/commit/66563102cd22ca95954b8b0d0f2502c74c3aed3c))
- Replace `attach_id` with `topic_id` in ajax torrent view** - ([c6d5b63](https://github.com/torrentpier/torrentpier/commit/c6d5b634ce1cd1388a7d7aa77aa946b4fa576887))
- Standardize topic handling in moderator actions** - ([8a9fa62](https://github.com/torrentpier/torrentpier/commit/8a9fa62e7a4063d0b0a5ee1edabbab8fb36cf62e))
- Remove legacy download count logic in user stats query** - ([4c715c6](https://github.com/torrentpier/torrentpier/commit/4c715c6a7a42fde2b338b60f4823b52c79d146de))
- Remove legacy attachment-related language entries** - ([ac8cf1a](https://github.com/torrentpier/torrentpier/commit/ac8cf1a3f553b5455b32c34c4faeeecc52779926))
- Replace `attach_id` with `topic_id` across ajax library** - ([2cb12f0](https://github.com/torrentpier/torrentpier/commit/2cb12f09660d9f44341ad57ca66519b1cae5595b))

### 🚜 Refactor

- Migrate terms.tpl to terms.twig and enhance variable handling in Template class - ([f6db2d0](https://github.com/torrentpier/torrentpier/commit/f6db2d0c04da6197b278cb1d221d50262639588d))
- Remove redundant `Template::$instance` and improve block data initialization - ([d236a0c](https://github.com/torrentpier/torrentpier/commit/d236a0c87384d338ebaf6ed90985518b35b89f23))
- Update torrent attachment checks and download count logic - ([536dc8c](https://github.com/torrentpier/torrentpier/commit/536dc8c56ab5e8a79580cf8804ebff8f54dd64c1))
- Centralize torrent filename generation logic - ([1fcd205](https://github.com/torrentpier/torrentpier/commit/1fcd205d1eda4535396a969e5cd4a71a0bfd6897))
- Improve attachment handling and template logic - ([3834565](https://github.com/torrentpier/torrentpier/commit/38345654bff91d66fcd5c3cedc52ee8190cf8db8))
- Remove `topic_attachment` field and update attachment checks - ([94f53f6](https://github.com/torrentpier/torrentpier/commit/94f53f6bc3ea9449a87320a5f99c66435f80ee8d))

### 🧪 Testing

- Add missing `DBG_USER` constant definition in `TemplateGracefulFallbackTest` - ([1f04619](https://github.com/torrentpier/torrentpier/commit/1f04619f7eb381a68c3dce97c0ff4770501ea4b2))

### ⚙️ Miscellaneous

- Remove `_attach.php` and deprecated attachment handling logic - ([e2bb8ce](https://github.com/torrentpier/torrentpier/commit/e2bb8ce311319542cd46a3c0b837111cc5772d82))


## [v2.8.9](https://github.com/torrentpier/torrentpier/compare/v2.8.8..v2.8.9) (2025-11-28)

### 🚀 Features

- *(cron)* Add execution time tracking for cron jobs ([#2220](https://github.com/torrentpier/torrentpier/pull/2220)) - ([66b6dcf](https://github.com/torrentpier/torrentpier/commit/66b6dcf9d48b5fe580eaa6b0f5b1f7b74948ffa8))
- *(install)* Add database drop option during installation ([#2226](https://github.com/torrentpier/torrentpier/pull/2226)) - ([ec0fb14](https://github.com/torrentpier/torrentpier/commit/ec0fb14c3a85840a721d4eb52307589c43bebc99))
- Add option to apply forum permissions to subforums ([#2223](https://github.com/torrentpier/torrentpier/pull/2223)) - ([1bcfd24](https://github.com/torrentpier/torrentpier/commit/1bcfd2437c5ee424952107e3983cfc5dece4ba9f))

### 🐛 Bug Fixes

- *(css)* Add dark mode support for latest news section ([#2230](https://github.com/torrentpier/torrentpier/pull/2230)) - ([704a71f](https://github.com/torrentpier/torrentpier/commit/704a71f870185f0b22ddb448f4509dde269ecb03))
- *(php8.5)* Add PHP 8.5 compatibility ([#2207](https://github.com/torrentpier/torrentpier/pull/2207)) - ([a0dd7a5](https://github.com/torrentpier/torrentpier/commit/a0dd7a54db3d86d11d1723952e2c47458d19917e))
- *(search)* Improve Manticore Search stability and query escaping ([#2225](https://github.com/torrentpier/torrentpier/pull/2225)) - ([89f520c](https://github.com/torrentpier/torrentpier/commit/89f520cf3a8e2c0af0296de94ae6a6f83fa46c04))
- *(security)* Prevent SQL injection in moderator panel topic_id parameter ([#2216](https://github.com/torrentpier/torrentpier/pull/2216)) - ([6a0f649](https://github.com/torrentpier/torrentpier/commit/6a0f6499d89fa5d6e2afa8ee53802a1ad11ece80))

### 📦 Dependencies

- *(deps)* Bump arokettu/torrent-file from 5.3.3 to 5.3.4 ([#2211](https://github.com/torrentpier/torrentpier/pull/2211)) - ([99ed126](https://github.com/torrentpier/torrentpier/commit/99ed1267589d727563eea1360291690d80260ee0))
- *(deps)* Bump symfony/mailer from 7.3.4 to 7.3.5 ([#2212](https://github.com/torrentpier/torrentpier/pull/2212)) - ([6402b78](https://github.com/torrentpier/torrentpier/commit/6402b7873320a23b8e832fa04eda8ebf2cde43c1))
- *(deps)* Bump nette/database from 3.2.7 to 3.2.8 ([#2213](https://github.com/torrentpier/torrentpier/pull/2213)) - ([b5153fd](https://github.com/torrentpier/torrentpier/commit/b5153fd2f09452ebbc2330412b4f6c4e6842868c))
- *(deps)* Bump twig/twig from 3.21.1 to 3.22.0 ([#2214](https://github.com/torrentpier/torrentpier/pull/2214)) - ([3002647](https://github.com/torrentpier/torrentpier/commit/30026476f17e31167448512f173586c7857138c8))
- *(deps-dev)* Bump symfony/var-dumper from 7.3.4 to 7.3.5 ([#2210](https://github.com/torrentpier/torrentpier/pull/2210)) - ([3055554](https://github.com/torrentpier/torrentpier/commit/305555474442e04f2044dd8073cc9a26570f3607))

### 🚜 Refactor

- *(admin)* Migrate stats pages to ajax with Nette Explorer ([#2227](https://github.com/torrentpier/torrentpier/pull/2227)) - ([7e7d17f](https://github.com/torrentpier/torrentpier/commit/7e7d17f80cdd7afc18a44ccb1c7c7d39bbd23549))
- *(datetime)* Integrate Carbon for date formatting ([#2208](https://github.com/torrentpier/torrentpier/pull/2208)) - ([d749c9b](https://github.com/torrentpier/torrentpier/commit/d749c9b48134f0d5ae2dde5f426c1f6e75e3bc80))
- *(image)* [**breaking**] Migrate from SimpleImage to Intervention Image v3 ([#2229](https://github.com/torrentpier/torrentpier/pull/2229)) - ([39ffdfd](https://github.com/torrentpier/torrentpier/commit/39ffdfd6119a0ec7e44e98e82005967bd2558eae))
- *(search)* Use `$dl_link_css` instead of hardcoded class names ([#2221](https://github.com/torrentpier/torrentpier/pull/2221)) - ([2d873e9](https://github.com/torrentpier/torrentpier/commit/2d873e9dbb7e35637b5ac4b82365798a19feb9ba))
- *(styles)* Remove empty selectors and duplicate rules ([#2218](https://github.com/torrentpier/torrentpier/pull/2218)) - ([d61c587](https://github.com/torrentpier/torrentpier/commit/d61c587bb91af75f0df4b48333af656e9de1df66))
- *(timezone)* Replace `$lang['TZ']` with centralized configuration for timezone management - ([a9d1158](https://github.com/torrentpier/torrentpier/commit/a9d1158e4eeb6d77e1fe180d8ec8b1f137777084))
- Remove deprecated `set_tpl_vars_lang` method ([#2217](https://github.com/torrentpier/torrentpier/pull/2217)) - ([c0c132d](https://github.com/torrentpier/torrentpier/commit/c0c132df3f6be3e146f144da4c97be195bad45a6))

### ⚙️ Miscellaneous

- *(i18n)* Update composer.lock with latest dependencies and translations updates - ([458d6d5](https://github.com/torrentpier/torrentpier/commit/458d6d562d301e205b57416061df2666c1d1c523))


## [v2.8.8](https://github.com/torrentpier/torrentpier/compare/v2.8.7..v2.8.8) (2025-10-21)

### 🚀 Features

- *(cache)* Add memcached support with automatic fallback to file storage ([#2205](https://github.com/torrentpier/torrentpier/pull/2205)) - ([0894274](https://github.com/torrentpier/torrentpier/commit/0894274ae81a20f6fcd9d1ff3f3577848e0a6a5a))
- *(email)* Migrate email templates from HTML placeholders to Twig ([#2198](https://github.com/torrentpier/torrentpier/pull/2198)) - ([3a29494](https://github.com/torrentpier/torrentpier/commit/3a29494ba13326975ab7f382873783bb9c4394c0))
- *(ranks)* Add visual image selector for rank administration ([#2189](https://github.com/torrentpier/torrentpier/pull/2189)) - ([4df9d0d](https://github.com/torrentpier/torrentpier/commit/4df9d0dc827cbd6591494a19390964082e009bf0))
- [**breaking**] Modernize Atom feed generation with dynamic on-the-fly rendering ([#2200](https://github.com/torrentpier/torrentpier/pull/2200)) - ([8b45e8d](https://github.com/torrentpier/torrentpier/commit/8b45e8d446a280a59b20a61819b1992babdc0102))
- Implement comprehensive dark mode with user preference persistence ([#2179](https://github.com/torrentpier/torrentpier/pull/2179)) - ([c77b2ff](https://github.com/torrentpier/torrentpier/commit/c77b2fffda24ff6b475aa9f0f578c04067e24668))

### 🐛 Bug Fixes

- *(admin)* Prevent error when managing permissions with no forums ([#2190](https://github.com/torrentpier/torrentpier/pull/2190)) - ([ed6b0d1](https://github.com/torrentpier/torrentpier/commit/ed6b0d13a2697912062a194427e49d95838c4736))
- *(database)* Prevent connection destruction during shutdown functions ([#2206](https://github.com/torrentpier/torrentpier/pull/2206)) - ([6fbe294](https://github.com/torrentpier/torrentpier/commit/6fbe294c4ea983c8df927bedc3645eb3b5234555))
- *(email)* Improve grammar, punctuation, and consistency in email templates - ([c384ba5](https://github.com/torrentpier/torrentpier/commit/c384ba5db7e3daf6bdd94e57339e8cdd54d162ab))
- *(email)* Correct punctuation in topic notification template - ([f41761e](https://github.com/torrentpier/torrentpier/commit/f41761e71e36160aba995456785fba265b1b5435))
- *(ranks)* Remove unnecessary user_rank reset on rank save ([#2188](https://github.com/torrentpier/torrentpier/pull/2188)) - ([afb2606](https://github.com/torrentpier/torrentpier/commit/afb2606549cb8415b379c2c38306fd0eff4ee57f))

### 📦 Dependencies

- *(deps)* Bump symfony/mailer from 7.3.3 to 7.3.4 ([#2181](https://github.com/torrentpier/torrentpier/pull/2181)) - ([b994bc1](https://github.com/torrentpier/torrentpier/commit/b994bc1b1b07011512ac13c425eaa6d233cd0efe))
- *(deps)* Bump symfony/polyfill from 1.32.0 to 1.33.0 ([#2180](https://github.com/torrentpier/torrentpier/pull/2180)) - ([10206fe](https://github.com/torrentpier/torrentpier/commit/10206fec169f4b46d9c8bbaabd1f3bcddf8de15f))
- *(deps)* Bump arokettu/torrent-file from 5.3.2 to 5.3.3 ([#2185](https://github.com/torrentpier/torrentpier/pull/2185)) - ([becae8b](https://github.com/torrentpier/torrentpier/commit/becae8b7a3a2b609ab6bfa08036287fd88ae69ec))
- *(deps)* Bump arokettu/bencode from 4.3.1 to 4.3.2 ([#2182](https://github.com/torrentpier/torrentpier/pull/2182)) - ([5640848](https://github.com/torrentpier/torrentpier/commit/5640848277232be9077e168af643f73cf457a36a))
- *(deps)* Bump arokettu/monsterid from 4.1.0 to 4.1.1 ([#2183](https://github.com/torrentpier/torrentpier/pull/2183)) - ([27cdd74](https://github.com/torrentpier/torrentpier/commit/27cdd743d6bd108bf21530a50cb485b77e61b37b))
- *(deps-dev)* Bump symfony/var-dumper from 7.3.3 to 7.3.4 ([#2184](https://github.com/torrentpier/torrentpier/pull/2184)) - ([9044a15](https://github.com/torrentpier/torrentpier/commit/9044a15dfc1bba84e107d0c0574b9c1f1ca0fb80))

### 💼 Other

- New Crowdin updates ([#2186](https://github.com/torrentpier/torrentpier/pull/2186)) - ([b06ce88](https://github.com/torrentpier/torrentpier/commit/b06ce88b8e84618dfa627758cde7df5711a98a01))

### 🚜 Refactor

- *(attachments)* [**breaking**] Remove quota limits system ([#2196](https://github.com/torrentpier/torrentpier/pull/2196)) - ([f2f3579](https://github.com/torrentpier/torrentpier/commit/f2f35794de6eced281ba64d8c60201ea38861edc))
- *(i18n)* Remove unused language keys and fix hardcoded strings ([#2197](https://github.com/torrentpier/torrentpier/pull/2197)) - ([d104689](https://github.com/torrentpier/torrentpier/commit/d10468995d90b96b90c47117081f462fc5fb06cc))
- *(i18n)* Move country list to configuration and optimize flag rendering logic ([#2193](https://github.com/torrentpier/torrentpier/pull/2193)) - ([5f1209c](https://github.com/torrentpier/torrentpier/commit/5f1209c9c6a3e0717ed480f8fd92f7b57e16d7a7))
- *(i18n)* Improve language strings and dynamic registration intervals ([#2192](https://github.com/torrentpier/torrentpier/pull/2192)) - ([245378d](https://github.com/torrentpier/torrentpier/commit/245378d063583855ea6e4fd19a0aeebe888c70f1))
- *(i18n)* Migrate from Crowdin to translation package system ([#2191](https://github.com/torrentpier/torrentpier/pull/2191)) - ([fdc0798](https://github.com/torrentpier/torrentpier/commit/fdc07988f82af96bae20b1ff19c5351560667116))
- Replace delta_time with humanTime using Carbon library ([#2204](https://github.com/torrentpier/torrentpier/pull/2204)) - ([f2139ab](https://github.com/torrentpier/torrentpier/commit/f2139abea9c8d2b0fbdc0217d447b5f7ef302cf4))
- Replace php-curl-class with centralized HttpClient in TorrServerAPI ([#2202](https://github.com/torrentpier/torrentpier/pull/2202)) - ([dd518af](https://github.com/torrentpier/torrentpier/commit/dd518af918e91bcfa8cbb92f66d6f56078050647))
- Introduce centralized HTTP client with Guzzle integration ([#2201](https://github.com/torrentpier/torrentpier/pull/2201)) - ([077e4ce](https://github.com/torrentpier/torrentpier/commit/077e4ce5b4f2111b2d1948f68d0403f2f96d507b))

### 🧪 Testing

- Trigger source sync (removal) - ([6c3498a](https://github.com/torrentpier/torrentpier/commit/6c3498a6aacff97665f30be6f928611a70e2135b))
- Trigger source sync - ([b350301](https://github.com/torrentpier/torrentpier/commit/b350301e1232537c816e8fc10c1adf9341b35246))

### ⚙️ Miscellaneous

- *(i18n)* Update country list to use configuration and expand translation grouping logic - ([fb74d40](https://github.com/torrentpier/torrentpier/commit/fb74d406cacb88a282eafde1818458601ad10f47))
- *(i18n)* Standardize and improve grammar, spelling, and consistency across language strings in main.php ([#2195](https://github.com/torrentpier/torrentpier/pull/2195)) - ([df5d213](https://github.com/torrentpier/torrentpier/commit/df5d21336fb6bed2baf7a9c73f6ea58a298c682a))
- *(language)* Update composer.lock to latest translations reference - ([f5d270b](https://github.com/torrentpier/torrentpier/commit/f5d270b9f834b2c052f0a8cee6f25fb941a0a8ed))
- *(language)* Update composer.lock for 'league/flysystem' and 'torrentpier/translations' dependencies to latest versions - ([81eb9bb](https://github.com/torrentpier/torrentpier/commit/81eb9bb77876407f1e464e02af38da717604d2ec))
- *(language)* Update composer.lock for updated dependencies (translations and webmozart/assert) - ([4bcb45d](https://github.com/torrentpier/torrentpier/commit/4bcb45d2bf7386c60629375a6f64e6e4854275bd))
- *(language)* Update torrentpier/translations to latest reference in composer.lock - ([320a3ca](https://github.com/torrentpier/torrentpier/commit/320a3cac18efeafc0428dbdcfe4347e28291a191))
- Remove ICQ and Skype user fields and associated references ([#2194](https://github.com/torrentpier/torrentpier/pull/2194)) - ([f61d925](https://github.com/torrentpier/torrentpier/commit/f61d925f1c1728585327e8745bb5b9f5451809cb))


## [v2.8.7](https://github.com/torrentpier/torrentpier/compare/v2.8.6..v2.8.7) (2025-10-10)

### 🐛 Bug Fixes

- *(updater)* Add fallback to `zipball_url` when no assets available ([#2175](https://github.com/torrentpier/torrentpier/pull/2175)) - ([8338ab1](https://github.com/torrentpier/torrentpier/commit/8338ab1f0cac7241edd05bef042c7c2df1f5d5d3))
- Undefined `humn_size()` in `EnhancedPrettyPageHandler.php` ([#2174](https://github.com/torrentpier/torrentpier/pull/2174)) - ([8349c11](https://github.com/torrentpier/torrentpier/commit/8349c118f162032ab305d43c815578921a22870c))


## [v2.8.6](https://github.com/torrentpier/torrentpier/compare/v2.8.5..v2.8.6) (2025-10-10)

### 🚀 Features

- Added `Manticore` search engine support 🔎 ([#2158](https://github.com/torrentpier/torrentpier/pull/2158)) - ([1011928](https://github.com/torrentpier/torrentpier/commit/101192896618518368f558bd397c2dddfd0f7065))

### 🐛 Bug Fixes

- *(docker)* Move database migrations from build to runtime ([#2172](https://github.com/torrentpier/torrentpier/pull/2172)) - ([118d349](https://github.com/torrentpier/torrentpier/commit/118d3490c30381d40c8ca783ca8988b882e743c5))
- *(docker)* Correct entrypoint and migration handling ([#2144](https://github.com/torrentpier/torrentpier/pull/2144)) - ([1822c90](https://github.com/torrentpier/torrentpier/commit/1822c907ebd7bdaff996f1a8fcc8d64a88ec8888))
- *(manticore)* Implement correct partial field updates with UPDATE/REPLACE strategy ([#2173](https://github.com/torrentpier/torrentpier/pull/2173)) - ([968da37](https://github.com/torrentpier/torrentpier/commit/968da3751d8c455387edc75f819deaf259b7f4d3))

### 🗑️ Removed

- Legacy `Sphinx API` support ([#2153](https://github.com/torrentpier/torrentpier/pull/2153)) - ([4ba315d](https://github.com/torrentpier/torrentpier/commit/4ba315db37144a6f38dcd3fa5437dd346df990d2))
- Deploy pipeline ([#2145](https://github.com/torrentpier/torrentpier/pull/2145)) - ([7cca19d](https://github.com/torrentpier/torrentpier/commit/7cca19d11d94d200f681da0e12dbb9638761a9fb))

### 💼 Other

- New Crowdin updates ([#2154](https://github.com/torrentpier/torrentpier/pull/2154)) - ([bf9be51](https://github.com/torrentpier/torrentpier/commit/bf9be510ce7e2e38ccb2ead9c000c85b89f06a63))

### 🚜 Refactor

- `IsHelper` class renamed to `HttpHelper` ([#2160](https://github.com/torrentpier/torrentpier/pull/2160)) - ([c97dd8e](https://github.com/torrentpier/torrentpier/commit/c97dd8e0dcefd37346e09c1796c3c87d334fca8f))

### ⚙️ Miscellaneous

- Updated README ([#2157](https://github.com/torrentpier/torrentpier/pull/2157)) - ([6d152e8](https://github.com/torrentpier/torrentpier/commit/6d152e81c32c3c514d4a496bcbef4edc77b1a45a))
- Moved scripts into `scripts` folder ([#2156](https://github.com/torrentpier/torrentpier/pull/2156)) - ([f11947d](https://github.com/torrentpier/torrentpier/commit/f11947db3517ac95f5a0799d591b5b9847216a8b))
- Force `production` environment if entered incorrect environment name ([#2152](https://github.com/torrentpier/torrentpier/pull/2152)) - ([048186c](https://github.com/torrentpier/torrentpier/commit/048186c3564426dc033d789c8cdd48965e0d8176))
- Added `EXCLUDED_USERS_CSV` alias for `EXCLUDED_USERS` ([#2151](https://github.com/torrentpier/torrentpier/pull/2151)) - ([ff45ef4](https://github.com/torrentpier/torrentpier/commit/ff45ef4e953aab46b2e9db708b1c089f9a848c98))
- Add PHP 8.2–8.4 test matrix with Pest ([#2150](https://github.com/torrentpier/torrentpier/pull/2150)) - ([881b277](https://github.com/torrentpier/torrentpier/commit/881b277ae8dc0feca8bfb05cef8081a1cd5dde64))
- Minor improvements ([#2149](https://github.com/torrentpier/torrentpier/pull/2149)) - ([9ab089c](https://github.com/torrentpier/torrentpier/commit/9ab089ca469d7a09dd609243466b5570e5f66bd3))


## [v2.8.5](https://github.com/torrentpier/torrentpier/compare/v2.8.4.1..v2.8.5) (2025-09-18)

### 🐛 Bug Fixes

- *(PHP 8.4)* Replace `trigger_error` with `RuntimeException` ([#2142](https://github.com/torrentpier/torrentpier/pull/2142)) - ([4112eb9](https://github.com/torrentpier/torrentpier/commit/4112eb99c0b65915c70bdb8e94a12f5e4a3baf0d))
- *(posting.php)* Use `auth_mod` instead of `IS_AM` when checking robots indexing ([#2140](https://github.com/torrentpier/torrentpier/pull/2140)) - ([4f91777](https://github.com/torrentpier/torrentpier/commit/4f91777a9b625a16bfd46e93a04d4c260462a22f))
- Use `development` environment instead of `local` ([#2143](https://github.com/torrentpier/torrentpier/pull/2143)) - ([9b6ba59](https://github.com/torrentpier/torrentpier/commit/9b6ba595a412fe5c0b3ea37c7ff87587c13b4e77))
- Passkey not showing when user ratio disabled ([#2141](https://github.com/torrentpier/torrentpier/pull/2141)) - ([9b5aee5](https://github.com/torrentpier/torrentpier/commit/9b5aee59cd49347ff1fda4b6d396ce02656f1037))

### 💼 Other

- New Crowdin updates ([#2135](https://github.com/torrentpier/torrentpier/pull/2135)) - ([7a23dee](https://github.com/torrentpier/torrentpier/commit/7a23dee141a8ef565bfbee384b71064be9c76279))

### 🚜 Refactor

- *(admin_ug_auth.php)* Simplify user level select rendering ([#2139](https://github.com/torrentpier/torrentpier/pull/2139)) - ([da4cf1a](https://github.com/torrentpier/torrentpier/commit/da4cf1ae62649d5da75fea3313aa2c2cea908614))


## [v2.8.4.1](https://github.com/torrentpier/torrentpier/compare/v2.8.4..v2.8.4.1) (2025-09-14)

### 🚀 Features

- *(log action)* Show poll (create, finish, edit, delete) actions ([#2132](https://github.com/torrentpier/torrentpier/pull/2132)) - ([8f970c1](https://github.com/torrentpier/torrentpier/commit/8f970c119307161c2ec6d619630827dac26fecc2))

### 🐛 Bug Fixes

- `Undefined variable $bb_cfg` & use new syntax for config variables ([#2134](https://github.com/torrentpier/torrentpier/pull/2134)) - ([eddc773](https://github.com/torrentpier/torrentpier/commit/eddc7734d1f02c55965a45973dbdeb7112beeaf2))

### ⚙️ Miscellaneous

- Hide `ratio_url_help` when ratio disabled ([#2133](https://github.com/torrentpier/torrentpier/pull/2133)) - ([9c038b4](https://github.com/torrentpier/torrentpier/commit/9c038b49704a8103c815b39bf0e15df1abe75a62))


## [v2.8.4](https://github.com/torrentpier/torrentpier/compare/v2.8.3..v2.8.4) (2025-09-14)

### 🚀 Features

- *(installer)* Add web server config guidance post-installation ([#2086](https://github.com/torrentpier/torrentpier/pull/2086)) - ([414c916](https://github.com/torrentpier/torrentpier/commit/414c9169f68e23ba6214f59de5dd2a5d7d63db69))
- *(log action)* Show `torrent delete` action ([#2061](https://github.com/torrentpier/torrentpier/pull/2061)) - ([4e79ea1](https://github.com/torrentpier/torrentpier/commit/4e79ea1476e9e302d04255606351f136cf4582d7))
- *(log action)* Show torrent register action ([#2060](https://github.com/torrentpier/torrentpier/pull/2060)) - ([8507d62](https://github.com/torrentpier/torrentpier/commit/8507d620cef1d74f50a6d9b569f976a5a055654d))
- *(view_torrent.php)* Added checking auth to download ([#2067](https://github.com/torrentpier/torrentpier/pull/2067)) - ([f02df3d](https://github.com/torrentpier/torrentpier/commit/f02df3d34c612f4ea7a514762069c27e9d2b9054))
- *(vote topic)* Improved functionality & implemented caching ([#2063](https://github.com/torrentpier/torrentpier/pull/2063)) - ([b48a7bc](https://github.com/torrentpier/torrentpier/commit/b48a7bc66f37769acad83ac095da7fc0204a51f4))
- Bring back `bb_exit()` & `prn_r()` functions ([#2114](https://github.com/torrentpier/torrentpier/pull/2114)) - ([3dc5826](https://github.com/torrentpier/torrentpier/commit/3dc5826a5a35c23c8f54d9d06b9fa56a2a869c0a))
- Allow setting custom ban reason when banning users ([#2094](https://github.com/torrentpier/torrentpier/pull/2094)) - ([006ea21](https://github.com/torrentpier/torrentpier/commit/006ea210c4dd33e2c3eba6fba2bd7c9a8439dd9b))
- Bring back support `seo_url` function in `Sitemap.php` ([#2093](https://github.com/torrentpier/torrentpier/pull/2093)) - ([f3027f4](https://github.com/torrentpier/torrentpier/commit/f3027f461a9b1d18461639e10751eb28aacda235))
- Add system information dashboard to admin panel ([#2092](https://github.com/torrentpier/torrentpier/pull/2092)) - ([479696e](https://github.com/torrentpier/torrentpier/commit/479696ed72acb3bb7a8848b91a9c63b6258f8f26))
- Enhance client IP detection with trusted proxy validation ([#2085](https://github.com/torrentpier/torrentpier/pull/2085)) - ([c3cb8b6](https://github.com/torrentpier/torrentpier/commit/c3cb8b665609b1b2950a90de0c89bb9da55fbd81))
- Add clear button for file upload input in `posting_attach.tpl` ([#2072](https://github.com/torrentpier/torrentpier/pull/2072)) - ([13e2603](https://github.com/torrentpier/torrentpier/commit/13e2603e90b852461caee6f7e937be0936531212))
- Prevent robots indexing for private topics ([#2071](https://github.com/torrentpier/torrentpier/pull/2071)) - ([9243b12](https://github.com/torrentpier/torrentpier/commit/9243b12a44e8a0333a2552be83f28da23c3bd07d))
- Added check for frozen torrent in `playback_m3u.php` ([#2065](https://github.com/torrentpier/torrentpier/pull/2065)) - ([20184a5](https://github.com/torrentpier/torrentpier/commit/20184a5e5ddcf54711ab6b3a4c9ce3be24f8a388))
- Add option to use original torrent filenames for downloads ([#2064](https://github.com/torrentpier/torrentpier/pull/2064)) - ([9246868](https://github.com/torrentpier/torrentpier/commit/92468686fbc1130710d62b4b50a626ae3c50ebe6))
- Added check for demo-mode in `admin_robots.php` and `admin_sitemap.php` ([#2046](https://github.com/torrentpier/torrentpier/pull/2046)) - ([49931d1](https://github.com/torrentpier/torrentpier/commit/49931d167f4b8439e4e2fc1340f303903de2a844))
- Restore some deprecated code for backward compatibility ([#2028](https://github.com/torrentpier/torrentpier/pull/2028)) - ([4e91f59](https://github.com/torrentpier/torrentpier/commit/4e91f592efeca188bab218891b6c557cef14f9df))

### 🐛 Bug Fixes

- *(ACP)* A non-numeric value encountered for stats ([#2073](https://github.com/torrentpier/torrentpier/pull/2073)) - ([2055ef5](https://github.com/torrentpier/torrentpier/commit/2055ef5587aa3de5047e5be0d2ae7887af18774a))
- *(Attach.php)* Trying to access array offset on value of type null ([#2075](https://github.com/torrentpier/torrentpier/pull/2075)) - ([07b3c7f](https://github.com/torrentpier/torrentpier/commit/07b3c7f129f4f2c9b7373d87726381f44f842e0d))
- *(cookie)* Correct cookie value handling and add SameSite support ([#2115](https://github.com/torrentpier/torrentpier/pull/2115)) - ([1da3fc5](https://github.com/torrentpier/torrentpier/commit/1da3fc58909cc759dd872ec0a22ede9fe088de9e))
- *(i18n)* Support deep merge for nested translation keys ([#2131](https://github.com/torrentpier/torrentpier/pull/2131)) - ([e71bb24](https://github.com/torrentpier/torrentpier/commit/e71bb24f7b8c123595f4dbecd4a26a12d591551e))
- Prevent robots indexing for login & registration pages ([#2116](https://github.com/torrentpier/torrentpier/pull/2116)) - ([4e71b5c](https://github.com/torrentpier/torrentpier/commit/4e71b5c31d42896e090362806e1bcf72dd15c3c0))
- Prevent showing meta description if defined `HAS_DIED` ([#2070](https://github.com/torrentpier/torrentpier/pull/2070)) - ([7858cb4](https://github.com/torrentpier/torrentpier/commit/7858cb45961bbd8330bbc0108155553d5a3c15dd))
- Make `Ajax::$action` property nullable to handle missing POST parameter ([#2066](https://github.com/torrentpier/torrentpier/pull/2066)) - ([41e5de8](https://github.com/torrentpier/torrentpier/commit/41e5de8ae7decf6abf365c56dd9df45ccdd6e47f))
- Handle Nette DateTime objects in birthday validation ([#2032](https://github.com/torrentpier/torrentpier/pull/2032)) - ([6e7e3dd](https://github.com/torrentpier/torrentpier/commit/6e7e3dd9efde5f8dda4435f186e054697c85fd05))

### 📦 Dependencies

- Replace `belomaxorka/captcha` with `gregwar/captcha` ([#2069](https://github.com/torrentpier/torrentpier/pull/2069)) - ([656f1ae](https://github.com/torrentpier/torrentpier/commit/656f1ae81689aa6d7a11807fba4c84111f6cec86))

### 💼 Other

- New Crowdin updates ([#2127](https://github.com/torrentpier/torrentpier/pull/2127)) - ([1495a75](https://github.com/torrentpier/torrentpier/commit/1495a754825252cdcb372448ae3aa5b6b04604e6))

### 🚜 Refactor

- *(admin)* Remove redundant `dir` and `lang` attributes from html tag ([#2051](https://github.com/torrentpier/torrentpier/pull/2051)) - ([3412a70](https://github.com/torrentpier/torrentpier/commit/3412a7009491e6b3a99a38f576eb587a150197f3))

### ⚙️ Miscellaneous

- *(docker)* Configure MySQL charset and collation ([#2102](https://github.com/torrentpier/torrentpier/pull/2102)) - ([d1d97bc](https://github.com/torrentpier/torrentpier/commit/d1d97bc615845eb43655bcb1bd35cfa80e6c7f78))
- Some minor improvements & updated Docker setup instructions ([#2101](https://github.com/torrentpier/torrentpier/pull/2101)) - ([8d4ecd8](https://github.com/torrentpier/torrentpier/commit/8d4ecd85cbc6c08cf8e763f65e286759153c60e4))
- Added missing mysqli extension in README ([#2130](https://github.com/torrentpier/torrentpier/pull/2130)) - ([e20cadf](https://github.com/torrentpier/torrentpier/commit/e20cadf4be16c647ce721cd7f8a1236c07db5022))
- Force disable `reg_email_activation` in demo mode ([#2129](https://github.com/torrentpier/torrentpier/pull/2129)) - ([e51b128](https://github.com/torrentpier/torrentpier/commit/e51b1286d76041149c5da47dbaff648d27c8eff3))
- Minor improvements ([#2126](https://github.com/torrentpier/torrentpier/pull/2126)) - ([f758d38](https://github.com/torrentpier/torrentpier/commit/f758d38736a4650bf7a81cf965a0fd4b6d3a4b62))
- Docker support ([#2100](https://github.com/torrentpier/torrentpier/pull/2100)) - ([7388f47](https://github.com/torrentpier/torrentpier/commit/7388f47055d8e91c23db090892fc7ee585eb2dcc))
- Fixed incorrect installation guidlines in `README.md` ([#2090](https://github.com/torrentpier/torrentpier/pull/2090)) - ([4dc7662](https://github.com/torrentpier/torrentpier/commit/4dc7662b4c4ad4ad208a86cc0622dd324e3d7883))
- Use `text` captcha driver by default ([#2084](https://github.com/torrentpier/torrentpier/pull/2084)) - ([63dedfc](https://github.com/torrentpier/torrentpier/commit/63dedfcfa4e1d5090f777ceb93fa6cedd1787840))
- Some minor improvements ([#2076](https://github.com/torrentpier/torrentpier/pull/2076)) - ([ca337f6](https://github.com/torrentpier/torrentpier/commit/ca337f6143d0f751b7d86f34f4c005f2654beb2a))
- Some minor improvements ([#2068](https://github.com/torrentpier/torrentpier/pull/2068)) - ([b793a6e](https://github.com/torrentpier/torrentpier/commit/b793a6e13e91baac00c0cb8661d17c5e60d3f3bb))
- Removed deploy pipeline ([#2047](https://github.com/torrentpier/torrentpier/pull/2047)) - ([144aa05](https://github.com/torrentpier/torrentpier/commit/144aa0558d11062c102224c5fd94c1ab8f994da9))

### ◀️ Revert

- Demo mode: Save user language in cookies ([#2128](https://github.com/torrentpier/torrentpier/pull/2128)) - ([a5ad7ba](https://github.com/torrentpier/torrentpier/commit/a5ad7bad09853f7700c450c451a6c34a880a1b25))
- "refactor: Moved `Select` class into `Legacy\Common` ([#1846](https://github.com/torrentpier/torrentpier/pull/1846))" - ([d2f5971](https://github.com/torrentpier/torrentpier/commit/d2f5971d37a2e8ec01629108f7b40b9d2c800d5d))

## New Contributors ❤️

* @advancedalloy made their first contribution

## [v2.8.3](https://github.com/torrentpier/torrentpier/compare/v2.8.2..v2.8.3) (2025-07-03)

### 🚀 Features

- *(lang)* Added `RTL` languages support ([#2031](https://github.com/torrentpier/torrentpier/pull/2031)) - ([fd46d3d](https://github.com/torrentpier/torrentpier/commit/fd46d3d04ad3ab1453256b2ab620508e2ba33586))
- *(updater)* Added exceptions logging ([#2026](https://github.com/torrentpier/torrentpier/pull/2026)) - ([51f2c70](https://github.com/torrentpier/torrentpier/commit/51f2c70d81b910012cdecd111b5b92c1dfd0d6f6))

### 🚜 Refactor

- *(TorrentFileList)* Reduce duplication in root directory unset logic ([#2027](https://github.com/torrentpier/torrentpier/pull/2027)) - ([d4d8210](https://github.com/torrentpier/torrentpier/commit/d4d82101dd67c9f4cd86e0f6f909495696974354))


## [v2.8.2](https://github.com/torrentpier/torrentpier/compare/v2.8.1..v2.8.2) (2025-06-30)

### 🐛 Bug Fixes

- *(TorrentFileList)* Avoid `array_merge` reindexing for numeric folder names ([#2014](https://github.com/torrentpier/torrentpier/pull/2014)) - ([915e1d8](https://github.com/torrentpier/torrentpier/commit/915e1d817c61d2a4f0691b24ec1bc6577a9cd44b))

### 🚜 Refactor

- Use `DEFAULT_CHARSET` constant instead of hardcoded string ([#2011](https://github.com/torrentpier/torrentpier/pull/2011)) - ([7ac3359](https://github.com/torrentpier/torrentpier/commit/7ac335974baa44a8575bebb71ae2fbc0902d10e7))


## [v2.8.1](https://github.com/torrentpier/torrentpier/compare/v2.8.0..v2.8.1) (2025-06-24)

### 🐛 Bug Fixes

- *(filelist)* `Undefined property: FileTree::$length` when v2 torrent only ([#2004](https://github.com/torrentpier/torrentpier/pull/2004)) - ([7f4cc9d](https://github.com/torrentpier/torrentpier/commit/7f4cc9d3b9a5b87100f710cc60f636d6e7d5a34e))
- *(ip-api)* Add error handling and logging for freeipapi.com requests ([#2006](https://github.com/torrentpier/torrentpier/pull/2006)) - ([f1d6e74](https://github.com/torrentpier/torrentpier/commit/f1d6e74e5d4c74b6e12e9e742f60f62e71783d11))


## [v2.8.0](https://github.com/torrentpier/torrentpier/compare/v2.7.0..v2.8.0) (2025-06-21)

### 🐛 Bug Fixes

- *(template)* Handle L_ variables in template vars when not found in lang vars ([#1998](https://github.com/torrentpier/torrentpier/pull/1998)) - ([c6076c2](https://github.com/torrentpier/torrentpier/commit/c6076c2c278e9a423f3862670236b75bddeadd87))


## [v2.7.0](https://github.com/torrentpier/torrentpier/compare/v2.6.0..v2.7.0) (2025-06-21)

### 🚀 Features

- *(database)* Add visual markers for Nette Explorer queries in debug panel ([#1965](https://github.com/torrentpier/torrentpier/pull/1965)) - ([2fd3067](https://github.com/torrentpier/torrentpier/commit/2fd306704f21febee7d53f4b4531601ce0cb81ce))
- *(language)* Add new language variable for migration file and enhance template fallback logic ([#1984](https://github.com/torrentpier/torrentpier/pull/1984)) - ([a33574c](https://github.com/torrentpier/torrentpier/commit/a33574c28f2eb6267a74fa6c9d97fea86527157a))
- *(migrations)* Implement Phinx database migration system ([#1976](https://github.com/torrentpier/torrentpier/pull/1976)) - ([fbde8cd](https://github.com/torrentpier/torrentpier/commit/fbde8cd421c9048afe70ddb41d0a9ed26d3fbef5))
- *(test)* [**breaking**] Add comprehensive testing infrastructure with Pest PHP  ([#1979](https://github.com/torrentpier/torrentpier/pull/1979)) - ([cc9d412](https://github.com/torrentpier/torrentpier/commit/cc9d412522938a023bd2b8eb880c4d2dd307c82a))
- [**breaking**] Implement Language singleton with shorthand functions ([#1966](https://github.com/torrentpier/torrentpier/pull/1966)) - ([49717d3](https://github.com/torrentpier/torrentpier/commit/49717d3a687b95885fe9773f2597354aed4b2b60))

### 🐛 Bug Fixes

- *(database)* Update affected rows tracking in Database class ([#1980](https://github.com/torrentpier/torrentpier/pull/1980)) - ([4f9cc9f](https://github.com/torrentpier/torrentpier/commit/4f9cc9fe0f7f4a85c90001a3f5514efdf04836da))

### 🚜 Refactor

- *(database)* Enhance error logging and various fixes ([#1978](https://github.com/torrentpier/torrentpier/pull/1978)) - ([7aed6bc](https://github.com/torrentpier/torrentpier/commit/7aed6bc7d89f4ed31e7ed6c6eeecc6e08d348c24))
- *(database)* Rename DB to Database and extract debug functionality ([#1964](https://github.com/torrentpier/torrentpier/pull/1964)) - ([6c0219d](https://github.com/torrentpier/torrentpier/commit/6c0219d53c7544b7d8a6374c0d0848945d32ae17))
- *(stats)* Improve database row fetching in tr_stats.php ([#1985](https://github.com/torrentpier/torrentpier/pull/1985)) - ([728116d](https://github.com/torrentpier/torrentpier/commit/728116d6dc9cf4476cce572ced5e8a7ef529ead8))

### ⚙️ Miscellaneous

- Update minimum `PHP` requirement to `8.2` ([#1987](https://github.com/torrentpier/torrentpier/pull/1987)) - ([9b322c7](https://github.com/torrentpier/torrentpier/commit/9b322c7093a634669e9f17a32ac42500f44f2496))
- Removed useless `composer update` from workflows & installer ([#1986](https://github.com/torrentpier/torrentpier/pull/1986)) - ([423424e](https://github.com/torrentpier/torrentpier/commit/423424e9478e0772957014fb30f5e84158067af7))
- Added --no-dev composer flag for some workflows ([#1982](https://github.com/torrentpier/torrentpier/pull/1982)) - ([e9a9e09](https://github.com/torrentpier/torrentpier/commit/e9a9e095768ba68aa5d5058a3e152ffaec916117))
- Added `--no-dev` composer flag for some workflows ([#1981](https://github.com/torrentpier/torrentpier/pull/1981)) - ([e8cba5d](https://github.com/torrentpier/torrentpier/commit/e8cba5dd3fc83b616f83c24991f79dc7258c5df3))


## [v2.6.0](https://github.com/torrentpier/torrentpier/compare/v2.5.0..v2.6.0) (2025-06-18)

### 🚀 Features

- [**breaking**] Implement unified cache system with Nette Caching ([#1963](https://github.com/torrentpier/torrentpier/pull/1963)) - ([07a06a3](https://github.com/torrentpier/torrentpier/commit/07a06a33cd97b37f68b533a87cdb5f7578f2c86f))
- Replace legacy database layer with Nette Database implementation ([#1961](https://github.com/torrentpier/torrentpier/pull/1961)) - ([f50b914](https://github.com/torrentpier/torrentpier/commit/f50b914cc18f777d92002baf2c812a635d5eed4b))

### 🐛 Bug Fixes

- *(User)* Add null and array checks before session data operations ([#1962](https://github.com/torrentpier/torrentpier/pull/1962)) - ([e458109](https://github.com/torrentpier/torrentpier/commit/e458109eefc54d86a78a1ddb3954581524852516))


## [v2.5.0](https://github.com/torrentpier/torrentpier/compare/v2.4.6-alpha.4..v2.5.0) (2025-06-18)

### 🚀 Features

- [**breaking**] Implement centralized Config class to replace global $bb_cfg array ([#1953](https://github.com/torrentpier/torrentpier/pull/1953)) - ([bf9100f](https://github.com/torrentpier/torrentpier/commit/bf9100fbfa74768edb01c62636198a44739d9923))

### 🐛 Bug Fixes

- *(installer)* Strip protocol from TP_HOST to keep only hostname ([#1952](https://github.com/torrentpier/torrentpier/pull/1952)) - ([81bf67c](https://github.com/torrentpier/torrentpier/commit/81bf67c2be85d49e988b7802ca7e9738ff580031))
- *(sql)* Resolve only_full_group_by compatibility issues in tracker cleanup ([#1951](https://github.com/torrentpier/torrentpier/pull/1951)) - ([37a0675](https://github.com/torrentpier/torrentpier/commit/37a0675adfb02014e7068f4aa82301e29f39eab6))

### 📦 Dependencies

- *(deps)* Bump filp/whoops from 2.18.2 to 2.18.3 ([#1948](https://github.com/torrentpier/torrentpier/pull/1948)) - ([b477680](https://github.com/torrentpier/torrentpier/commit/b4776804a408217229caa327c79849cf13ce2aa5))

### 🚜 Refactor

- *(censor)* [**breaking**] Migrate Censor class to singleton pattern ([#1954](https://github.com/torrentpier/torrentpier/pull/1954)) - ([74a564d](https://github.com/torrentpier/torrentpier/commit/74a564d7954c6f8745ebcffdcd9c8997e371d47a))
- *(config)* [**breaking**] Encapsulate global $bb_cfg array in Config class ([#1950](https://github.com/torrentpier/torrentpier/pull/1950)) - ([5842994](https://github.com/torrentpier/torrentpier/commit/5842994782dfa62788f8427c55045abdbfb5b8e9))

### 📚 Documentation

- Add Select class migration guide ([#1960](https://github.com/torrentpier/torrentpier/pull/1960)) - ([86abafb](https://github.com/torrentpier/torrentpier/commit/86abafb11469d14a746d12725b15cf6b7015ec44))

### ⚙️ Miscellaneous

- *(_release.php)* Finally! Removed some useless params ([#1947](https://github.com/torrentpier/torrentpier/pull/1947)) - ([9c7d270](https://github.com/torrentpier/torrentpier/commit/9c7d270598c0153fb82f4b7ad96f5b59399b2159))
- *(cliff)* Add conventional commit prefix to changelog message ([#1957](https://github.com/torrentpier/torrentpier/pull/1957)) - ([b1b2618](https://github.com/torrentpier/torrentpier/commit/b1b26187579f6981165d85c316a3c5b7199ce2ee))



