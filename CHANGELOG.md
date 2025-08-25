[![TorrentPier](https://raw.githubusercontent.com/torrentpier/.github/refs/heads/main/versions/Cattle.png)](https://github.com/torrentpier)

# 📖 Change Log

## [v2.4.12](https://github.com/torrentpier/torrentpier/compare/v2.4.11..v2.4.12) (2025-08-25)

### 🚀 Features

- *(installer)* Add web server config guidance post-installation ([#2086](https://github.com/torrentpier/torrentpier/pull/2086)) - ([b1b8c23](https://github.com/torrentpier/torrentpier/commit/b1b8c23d93af2bb2ee3069ca9e87273a2675d546))
- *(log action)* Show poll (create, finish, edit, delete) actions ([#2095](https://github.com/torrentpier/torrentpier/pull/2095)) - ([36837f4](https://github.com/torrentpier/torrentpier/commit/36837f4bc9e8327b0115873a14551f925b4db3b7))
- Allow setting custom ban reason when banning users ([#2094](https://github.com/torrentpier/torrentpier/pull/2094)) - ([16e28a5](https://github.com/torrentpier/torrentpier/commit/16e28a5c41d775a9fbaae8dbc80f0c3ef89a2d65))
- Bring back support `seo_url` function in `Sitemap.php` ([#2093](https://github.com/torrentpier/torrentpier/pull/2093)) - ([af9bf73](https://github.com/torrentpier/torrentpier/commit/af9bf7382ca1318eef3f349efeddbd1ea32b28b1))
- Add system information dashboard to admin panel ([#2092](https://github.com/torrentpier/torrentpier/pull/2092)) - ([9a4d30c](https://github.com/torrentpier/torrentpier/commit/9a4d30c6daadf3d413239b1cf0c06373e174b672))
- Enhance client IP detection with trusted proxy validation ([#2085](https://github.com/torrentpier/torrentpier/pull/2085)) - ([1e3e58a](https://github.com/torrentpier/torrentpier/commit/1e3e58aeed66d73292c529eb2c3110408ae83e71))

### ⚙️ Miscellaneous

- *(ci)* Removed docker-publish.yml ([#2107](https://github.com/torrentpier/torrentpier/pull/2107)) - ([67af552](https://github.com/torrentpier/torrentpier/commit/67af55216999020bda7186e22301a626c95d3667))
- *(docker)* Configure MySQL charset and collation ([#2102](https://github.com/torrentpier/torrentpier/pull/2102)) - ([9b00d57](https://github.com/torrentpier/torrentpier/commit/9b00d571b14acb1c93515f98f9233b340dc9a7ea))
- Some minor improvements & updated Docker setup instructions ([#2101](https://github.com/torrentpier/torrentpier/pull/2101)) - ([01b5269](https://github.com/torrentpier/torrentpier/commit/01b5269f22eb3d926a5453767bc295364ad8210c))
- Docker support ([#2100](https://github.com/torrentpier/torrentpier/pull/2100)) - ([e7b1781](https://github.com/torrentpier/torrentpier/commit/e7b178146eea02b77c4d89cdfa02a4dfe34ade57))
- Added missing `mysqli` extension in README & updated some workflows ([#2091](https://github.com/torrentpier/torrentpier/pull/2091)) - ([7c3faa9](https://github.com/torrentpier/torrentpier/commit/7c3faa922f610810d06c05c7044e07ac18952593))
- Fixed incorrect installation guidlines in `README.md` ([#2090](https://github.com/torrentpier/torrentpier/pull/2090)) - ([2468b60](https://github.com/torrentpier/torrentpier/commit/2468b60363e303747c614bb4854f022c2abd32c2))
- Use `text` captcha driver by default ([#2084](https://github.com/torrentpier/torrentpier/pull/2084)) - ([7a393e3](https://github.com/torrentpier/torrentpier/commit/7a393e3548cf9866e322794b88e105da780ecbfe))


## [v2.4.11](https://github.com/torrentpier/torrentpier/compare/v2.4.10..v2.4.11) (2025-08-06)

### 🚀 Features

- *(log action)* Show `torrent delete` action ([#2061](https://github.com/torrentpier/torrentpier/pull/2061)) - ([f80cad0](https://github.com/torrentpier/torrentpier/commit/f80cad0c6f8606ae3314e6dc4962f027f12798a3))
- *(log action)* Show torrent register action ([#2060](https://github.com/torrentpier/torrentpier/pull/2060)) - ([66c01a5](https://github.com/torrentpier/torrentpier/commit/66c01a591f8618e911fd042cc23bf36902de8a38))
- *(view_torrent.php)* Added checking auth to download ([#2067](https://github.com/torrentpier/torrentpier/pull/2067)) - ([7e38c5b](https://github.com/torrentpier/torrentpier/commit/7e38c5b63cacaec4c974a423fd1a6c0352529fd6))
- *(vote topic)* Improved functionality & implemented caching ([#2063](https://github.com/torrentpier/torrentpier/pull/2063)) - ([e1337ef](https://github.com/torrentpier/torrentpier/commit/e1337ef5bc9cd7741d40cd645059019b5b5b576a))
- Add clear button for file upload input in `posting_attach.tpl` ([#2072](https://github.com/torrentpier/torrentpier/pull/2072)) - ([7c6ab0e](https://github.com/torrentpier/torrentpier/commit/7c6ab0eed4691fa0f1bd29f9e19f956fdb16b43e))
- Prevent robots indexing for private topics ([#2071](https://github.com/torrentpier/torrentpier/pull/2071)) - ([eecfe1a](https://github.com/torrentpier/torrentpier/commit/eecfe1a9515034d263a49f84210d3d32430253be))
- Added check for frozen torrent in `playback_m3u.php` ([#2065](https://github.com/torrentpier/torrentpier/pull/2065)) - ([57a9f3f](https://github.com/torrentpier/torrentpier/commit/57a9f3f7c67ca5c8db2218269a465bfc2d4644b4))
- Add option to use original torrent filenames for downloads ([#2064](https://github.com/torrentpier/torrentpier/pull/2064)) - ([07399fc](https://github.com/torrentpier/torrentpier/commit/07399fc00df214d1ad863e8a893495ace26ff7fa))
- Added check for demo-mode in `admin_robots.php` and `admin_sitemap.php` ([#2046](https://github.com/torrentpier/torrentpier/pull/2046)) - ([dd64236](https://github.com/torrentpier/torrentpier/commit/dd64236da12f7d76e0b548601c86f38164a71725))

### 🐛 Bug Fixes

- *(ACP)* A non-numeric value encountered for stats ([#2073](https://github.com/torrentpier/torrentpier/pull/2073)) - ([d690447](https://github.com/torrentpier/torrentpier/commit/d690447cdbdf7f07de9a539ef752dab69a205390))
- *(Attach.php)* Trying to access array offset on value of type null ([#2075](https://github.com/torrentpier/torrentpier/pull/2075)) - ([abb2b24](https://github.com/torrentpier/torrentpier/commit/abb2b242b75d1acc41e2d901f8b558e90a3b35d7))
- Prevent showing meta description if defined `HAS_DIED` ([#2070](https://github.com/torrentpier/torrentpier/pull/2070)) - ([3eba11f](https://github.com/torrentpier/torrentpier/commit/3eba11f26b3432ef7446de5bc938cbcbfa4932f5))
- Make `Ajax::$action` property nullable to handle missing POST parameter ([#2066](https://github.com/torrentpier/torrentpier/pull/2066)) - ([efd85ee](https://github.com/torrentpier/torrentpier/commit/efd85eef4ad69e5b53be3f3d0ea75d267d0b664c))

### 📦 Dependencies

- Replace `belomaxorka/captcha` with `gregwar/captcha` ([#2069](https://github.com/torrentpier/torrentpier/pull/2069)) - ([d12e6ba](https://github.com/torrentpier/torrentpier/commit/d12e6ba922d38c5c24380c58c037d405af5c38c9))

### 🚜 Refactor

- *(admin)* Remove redundant `dir` and `lang` attributes from html tag ([#2051](https://github.com/torrentpier/torrentpier/pull/2051)) - ([bb992fd](https://github.com/torrentpier/torrentpier/commit/bb992fd81b0fede511ab566fdc7bf4d25b924ae2))

### ⚙️ Miscellaneous

- Some minor improvements ([#2076](https://github.com/torrentpier/torrentpier/pull/2076)) - ([5ff296f](https://github.com/torrentpier/torrentpier/commit/5ff296f83be87a0cc5607fd352d62321819b0e03))
- Some minor improvements ([#2068](https://github.com/torrentpier/torrentpier/pull/2068)) - ([e3eb22e](https://github.com/torrentpier/torrentpier/commit/e3eb22e2d8d7b3bd2413cfcb1badfbd81ee9dc54))
- Added pull request template & updated workflow ([#2052](https://github.com/torrentpier/torrentpier/pull/2052)) - ([a863a61](https://github.com/torrentpier/torrentpier/commit/a863a619419881e831a6556fa5e77dae746ff031))


## [v2.4.10](https://github.com/torrentpier/torrentpier/compare/v2.4.9..v2.4.10) (2025-07-03)

### 🚀 Features

- *(lang)* Added `RTL` languages support ([#2031](https://github.com/torrentpier/torrentpier/pull/2031)) - ([9024640](https://github.com/torrentpier/torrentpier/commit/9024640d59428cc164fc6b10246ee40e333ec8e9))
- Restore some deprecated code for backward compatibility ([#2028](https://github.com/torrentpier/torrentpier/pull/2028)) - ([695864e](https://github.com/torrentpier/torrentpier/commit/695864ef6995a3c7b16ade822036c23908fc3aaf))

### ◀️ Revert

- "refactor: Moved `Select` class into `Legacy\Common` ([#1846](https://github.com/torrentpier/torrentpier/pull/1846))" - ([e697672](https://github.com/torrentpier/torrentpier/commit/e6976721dc2f6cde6a09b6a55e2f37e5f43f5932))


## [v2.4.9](https://github.com/torrentpier/torrentpier/compare/v2.4.8..v2.4.9) (2025-07-02)

### 🚀 Features

- *(updater)* Added exceptions logging ([#2026](https://github.com/torrentpier/torrentpier/pull/2026)) - ([57d0d59](https://github.com/torrentpier/torrentpier/commit/57d0d59b5379600f63cf7d5f774e3ec000e39473))

### 🚜 Refactor

- *(TorrentFileList)* Reduce duplication in root directory unset logic ([#2027](https://github.com/torrentpier/torrentpier/pull/2027)) - ([6840376](https://github.com/torrentpier/torrentpier/commit/68403760c1e9e01133536bb5021b08d9101d323e))


## [v2.4.8](https://github.com/torrentpier/torrentpier/compare/v2.4.7..v2.4.8) (2025-06-30)

### 🐛 Bug Fixes

- *(TorrentFileList)* Avoid `array_merge` reindexing for numeric folder names ([#2014](https://github.com/torrentpier/torrentpier/pull/2014)) - ([a5fbc2f](https://github.com/torrentpier/torrentpier/commit/a5fbc2ffc7389c30ffbb98d253ff8e936528fec1))
- *(redirect)* Add no-cache headers to prevent browser caching of redirects ([#2010](https://github.com/torrentpier/torrentpier/pull/2010)) - ([134b3df](https://github.com/torrentpier/torrentpier/commit/134b3dfa5cd8e8e5ce3f10912b58afecc4f118e0))

### 🚜 Refactor

- Use `DEFAULT_CHARSET` constant instead of hardcoded string ([#2011](https://github.com/torrentpier/torrentpier/pull/2011)) - ([c2cbc77](https://github.com/torrentpier/torrentpier/commit/c2cbc77b144057d3d37cd58b635eabc6280fe137))


## [v2.4.7](https://github.com/torrentpier/torrentpier/compare/v2.4.6..v2.4.7) (2025-06-24)

### 🐛 Bug Fixes

- *(filelist)* `Undefined property: FileTree::$length` when v2 torrent only ([#2004](https://github.com/torrentpier/torrentpier/pull/2004)) - ([8c161ce](https://github.com/torrentpier/torrentpier/commit/8c161ceae0f80a3ffe57da06dbadd1f9a53272f3))
- *(ip-api)* Add error handling and logging for freeipapi.com requests ([#2006](https://github.com/torrentpier/torrentpier/pull/2006)) - ([12ce6e7](https://github.com/torrentpier/torrentpier/commit/12ce6e783ec97a6c3df0e11273944a3e6cfe466d))

### 📚 Documentation

- Changed nightly.link url in `README.md` ([#1977](https://github.com/torrentpier/torrentpier/pull/1977)) - ([dc64426](https://github.com/torrentpier/torrentpier/commit/dc64426574087e69bc7e056a89ff367438e37344))
- Updated `Requirements` sections in README.md ([#1975](https://github.com/torrentpier/torrentpier/pull/1975)) - ([b7bc7f9](https://github.com/torrentpier/torrentpier/commit/b7bc7f91662b050082843d18b03376dc67efa3e0))
- Fixed some typos in `README.md` ([#1974](https://github.com/torrentpier/torrentpier/pull/1974)) - ([403fcf2](https://github.com/torrentpier/torrentpier/commit/403fcf2aca4b2d70bfca194107f4b4f5c5ba7f03))


## [v2.4.6](https://github.com/torrentpier/torrentpier/compare/v2.4.6-alpha.4..v2.4.6) (2025-06-19)

### 🐛 Bug Fixes

- *(installer)* Strip protocol from TP_HOST to keep only hostname ([#1969](https://github.com/torrentpier/torrentpier/pull/1969)) - ([15f9948](https://github.com/torrentpier/torrentpier/commit/15f994840331b135cd64c0cd61de95fecfc29db8))
- *(sql)* Resolve `only_full_group_by` compatibility issues in tracker cleanup - ([faf3d79](https://github.com/torrentpier/torrentpier/commit/faf3d7919249d869d8ca8d41617dd4356dc0ac48))
- Duplicate column SQL query issues in `viewtopic.php` ([#1973](https://github.com/torrentpier/torrentpier/pull/1973)) - ([6a1d682](https://github.com/torrentpier/torrentpier/commit/6a1d6823856dd7c3cef45bea2681828526d1b9f8))
- SQL queries in online_userlist.php to use MAX() for session data and adjusted GROUP BY clause for better accuracy ([#1971](https://github.com/torrentpier/torrentpier/pull/1971)) - ([2a8b6da](https://github.com/torrentpier/torrentpier/commit/2a8b6daecf63752b8a852c950e9a7fd08e17f57c))

### 📦 Dependencies

- *(deps)* Bump filp/whoops from 2.18.2 to 2.18.3 ([#1948](https://github.com/torrentpier/torrentpier/pull/1948)) - ([b477680](https://github.com/torrentpier/torrentpier/commit/b4776804a408217229caa327c79849cf13ce2aa5))

### ⚙️ Miscellaneous

- *(_release.php)* Finally! Removed some useless params ([#1947](https://github.com/torrentpier/torrentpier/pull/1947)) - ([9c7d270](https://github.com/torrentpier/torrentpier/commit/9c7d270598c0153fb82f4b7ad96f5b59399b2159))
- *(cliff)* Add conventional commit prefix to changelog message ([#1970](https://github.com/torrentpier/torrentpier/pull/1970)) - ([7d9594e](https://github.com/torrentpier/torrentpier/commit/7d9594eedab1b2c81d888dfba68ded1b8a142282))
- Changed active branch name in `_release.php` ([#1972](https://github.com/torrentpier/torrentpier/pull/1972)) - ([7dc69ba](https://github.com/torrentpier/torrentpier/commit/7dc69ba699c75d87c709a799291c4b544b3e92aa))
- Changed branch name from `master` to `v2.4` ([#1968](https://github.com/torrentpier/torrentpier/pull/1968)) - ([a8e252f](https://github.com/torrentpier/torrentpier/commit/a8e252f64f7205b7bb24739ab637144c6fb022d6))

## New Contributors ❤️

* @belomaxorka made their first contribution
* @dependabot[bot] made their first contribution in [#1948](https://github.com/torrentpier/torrentpier/pull/1948)

## [v2.4.6-alpha.4](https://github.com/torrentpier/torrentpier/compare/v2.4.6-alpha.3..v2.4.6-alpha.4) (2025-06-13)

### ⚙️ Miscellaneous

- *(_release.php)* Use `GPG` sign for tags by default ([#1946](https://github.com/torrentpier/torrentpier/pull/1946)) - ([0271b21](https://github.com/torrentpier/torrentpier/commit/0271b21a5e8c9dce918da9954547d81dae2a1d4b))


## [v2.4.6-alpha.3](https://github.com/torrentpier/torrentpier/compare/v2.4.6-alpha.2..v2.4.6-alpha.3) (2025-06-13)

### ⚙️ Miscellaneous

- *(_release.php)* Minor improvements ([#1945](https://github.com/torrentpier/torrentpier/pull/1945)) - ([e5811f9](https://github.com/torrentpier/torrentpier/commit/e5811f9c66eef7f228b51fb82ffda3bcddeb915d))


## [v2.4.6-alpha.2](https://github.com/torrentpier/torrentpier/compare/v2.4.6-alpha.1..v2.4.6-alpha.2) (2025-06-12)

### 🚀 Features

- *(ajax)* Log full ajax request/response data to console in explain mode ([#1942](https://github.com/torrentpier/torrentpier/pull/1942)) - ([bcf4eb4](https://github.com/torrentpier/torrentpier/commit/bcf4eb4e9baacf27e23a2b7c7135918ec3356c1a))
- Improved ajax debug ([#1941](https://github.com/torrentpier/torrentpier/pull/1941)) - ([6f03f75](https://github.com/torrentpier/torrentpier/commit/6f03f750bab400f5e8a74bd05c9ee167343959ab))
- Add console log for ajax actions when explain cookie is set ([#1940](https://github.com/torrentpier/torrentpier/pull/1940)) - ([345dd1b](https://github.com/torrentpier/torrentpier/commit/345dd1bc20928e25dc72befb705502156e47f1d7))

### 🐛 Bug Fixes

- Set `$datastore->enqueue` before `$datastore->get` ([#1937](https://github.com/torrentpier/torrentpier/pull/1937)) - ([bf328dd](https://github.com/torrentpier/torrentpier/commit/bf328dd69ec42e417275f037dc59a15a2867d7f4))

### 📦 Dependencies

- *(deps)* Bump filp/whoops from 2.18.1 to 2.18.2 ([#1943](https://github.com/torrentpier/torrentpier/pull/1943)) - ([9a52955](https://github.com/torrentpier/torrentpier/commit/9a529558b41f620e8347cc1091f59b1f2d864ca9))

### 🗑️ Removed

- `'cat_forums'` from enqueue list in `get_forum_mods` ajax ([#1939](https://github.com/torrentpier/torrentpier/pull/1939)) - ([28e38aa](https://github.com/torrentpier/torrentpier/commit/28e38aa78103c8233e15439ecd886187a55d5e12))
- Extra `CFG_DIR` constant ([#1936](https://github.com/torrentpier/torrentpier/pull/1936)) - ([4b16b84](https://github.com/torrentpier/torrentpier/commit/4b16b847f542e3608c8bb4d97d1f27f7fd6c97b7))

### ⚙️ Miscellaneous

- *(_release.php)* Minor improvements ([#1938](https://github.com/torrentpier/torrentpier/pull/1938)) - ([f9db78d](https://github.com/torrentpier/torrentpier/commit/f9db78d266ff3707e96b1b9d3d2330a507181012))
- *(_release.php)* Temporary removed automatic `push origin` ([#1935](https://github.com/torrentpier/torrentpier/pull/1935)) - ([dcd7002](https://github.com/torrentpier/torrentpier/commit/dcd7002c2aa09ec187f3afd91fb7e3f5e03630e0))
- *(_release.php)* Added ability to set version emoji ([#1934](https://github.com/torrentpier/torrentpier/pull/1934)) - ([75ef574](https://github.com/torrentpier/torrentpier/commit/75ef57474c3a32e86ecc98a5ff2fab39a9a66282))
- *(_release.php)* Added automatic `CHANGELOG.md` update ([#1933](https://github.com/torrentpier/torrentpier/pull/1933)) - ([867359a](https://github.com/torrentpier/torrentpier/commit/867359a89e480071cfd927e2cb6ef4fd761c0172))
- *(_release.php)* Added `push origin` command ([#1932](https://github.com/torrentpier/torrentpier/pull/1932)) - ([5561e00](https://github.com/torrentpier/torrentpier/commit/5561e0022ca6a0a668f2dc5aee541609bb6c4d1e))
- *(cliff.toml)* Use correct nightly link ([#1944](https://github.com/torrentpier/torrentpier/pull/1944)) - ([5e6fb3e](https://github.com/torrentpier/torrentpier/commit/5e6fb3ef424cbc84bb5e25625dcd22fd73ec98fa))


## [v2.4.6-alpha.1](https://github.com/torrentpier/torrentpier/compare/v2.4.5..v2.4.6-alpha.1) (2025-06-09)

### 🚀 Features

- *(ACP)* Added `robots.txt` editor ([#1913](https://github.com/torrentpier/torrentpier/pull/1913)) - ([79bb13e](https://github.com/torrentpier/torrentpier/commit/79bb13e17d07505be4d3a3c67223b4f591b66bfb))
- *(bbcode)* Added `[nfo]` and `[pre]` tags ([#1923](https://github.com/torrentpier/torrentpier/pull/1923)) - ([f64c340](https://github.com/torrentpier/torrentpier/commit/f64c340563378a364e1f00c64b17ac1c79531302))
- *(bbcode)* Implement color customization for `[box]` tag ([#1920](https://github.com/torrentpier/torrentpier/pull/1920)) - ([4c24cb6](https://github.com/torrentpier/torrentpier/commit/4c24cb65bfebf307b717e985b169ea5d27df64f8))
- *(install)* Autofill `Host` in `robots.txt` file ([#1916](https://github.com/torrentpier/torrentpier/pull/1916)) - ([03eeb08](https://github.com/torrentpier/torrentpier/commit/03eeb08ad185b6dcc99563f567297e41f4a56117))
- *(meta)* Minor improvements to description generation ([#1926](https://github.com/torrentpier/torrentpier/pull/1926)) - ([4d0b294](https://github.com/torrentpier/torrentpier/commit/4d0b2941e3ef6703ac2cd4c03524a93e688e0c39))
- Added ability to set page meta description ([#1917](https://github.com/torrentpier/torrentpier/pull/1917)) - ([7b8b9a0](https://github.com/torrentpier/torrentpier/commit/7b8b9a0bbabc1dfbf56cac8c105ad158ae78c3a7))

### 🈳 New translations

- New Crowdin updates ([#1925](https://github.com/torrentpier/torrentpier/pull/1925)) - ([2487d13](https://github.com/torrentpier/torrentpier/commit/2487d130bb23bd82cedf0d114843bb48f6d2e61c))
- New Crowdin updates ([#1924](https://github.com/torrentpier/torrentpier/pull/1924)) - ([0515670](https://github.com/torrentpier/torrentpier/commit/0515670bee99faa5f0979162096114bc9d3ddf98))
- New translations main.php (Russian) ([#1922](https://github.com/torrentpier/torrentpier/pull/1922)) - ([8e965fb](https://github.com/torrentpier/torrentpier/commit/8e965fb1ceb5e82201c43b33fcdb044256646191))
- New Crowdin updates ([#1921](https://github.com/torrentpier/torrentpier/pull/1921)) - ([daeb7fe](https://github.com/torrentpier/torrentpier/commit/daeb7fe87e8da53745fe7aac0708cefa3392ffdc))
- New Crowdin updates ([#1915](https://github.com/torrentpier/torrentpier/pull/1915)) - ([a3da6f5](https://github.com/torrentpier/torrentpier/commit/a3da6f538658fbfe4e57aad10046d8c459a1a498))
- New Crowdin updates ([#1914](https://github.com/torrentpier/torrentpier/pull/1914)) - ([a15baef](https://github.com/torrentpier/torrentpier/commit/a15baef69a2955b6dc9cd6e8fdf467550d0b5d09))
- New Crowdin updates ([#1911](https://github.com/torrentpier/torrentpier/pull/1911)) - ([174f441](https://github.com/torrentpier/torrentpier/commit/174f44160e1f33bed9422f0c4eab9d73b7025036))
- New Crowdin updates ([#1910](https://github.com/torrentpier/torrentpier/pull/1910)) - ([c40aad2](https://github.com/torrentpier/torrentpier/commit/c40aad20ad865849d3088498f1ba95a5fb0a0621))
- New Crowdin updates ([#1907](https://github.com/torrentpier/torrentpier/pull/1907)) - ([999ae1e](https://github.com/torrentpier/torrentpier/commit/999ae1eff9f3a4c951fc48efbf94c0cea2a5f8d2))
- Updated translations ([#1909](https://github.com/torrentpier/torrentpier/pull/1909)) - ([897edfc](https://github.com/torrentpier/torrentpier/commit/897edfc371087427c574776472cbbf3f1f933273))
- Updated translations ([#1908](https://github.com/torrentpier/torrentpier/pull/1908)) - ([6d0499d](https://github.com/torrentpier/torrentpier/commit/6d0499dd0229d454d3af00f10151adc26a9e96a7))
- New translations ([#1906](https://github.com/torrentpier/torrentpier/pull/1906)) - ([8a3b12c](https://github.com/torrentpier/torrentpier/commit/8a3b12c1192678552a3186c1f58df9b4d7e5ba1b))

### 📦 Dependencies

- *(deps)* Bump filp/whoops from 2.18.0 to 2.18.1 ([#1919](https://github.com/torrentpier/torrentpier/pull/1919)) - ([1253661](https://github.com/torrentpier/torrentpier/commit/125366147c6257abadd489f3802e4a0dab37a89c))
- *(deps)* Bump arokettu/bencode from 4.3.0 to 4.3.1 ([#1912](https://github.com/torrentpier/torrentpier/pull/1912)) - ([f76e351](https://github.com/torrentpier/torrentpier/commit/f76e351b32cfa2932bc1afde6c3c522cd993b8af))

### ⚙️ Miscellaneous

- *(_release.php)* Added `GPG` sign for tags ([#1931](https://github.com/torrentpier/torrentpier/pull/1931)) - ([8ecc617](https://github.com/torrentpier/torrentpier/commit/8ecc61719acb61e9a2ce115b28f1a82580c01110))
- *(cliff)* Added automated script for releases creation ([#1930](https://github.com/torrentpier/torrentpier/pull/1930)) - ([6adde35](https://github.com/torrentpier/torrentpier/commit/6adde35849811648bcb8fa1a72c3be0a886b7919))
- *(cliff)* Completely removed `cliff-releases.toml` ([#1929](https://github.com/torrentpier/torrentpier/pull/1929)) - ([cef041c](https://github.com/torrentpier/torrentpier/commit/cef041c0d128dca480ca40770f52385f868706b0))
- *(cliff)* Updated config ([#1928](https://github.com/torrentpier/torrentpier/pull/1928)) - ([212e5c5](https://github.com/torrentpier/torrentpier/commit/212e5c52832f32e8864850bf520b5c73f27f1609))
- Minor improvements ([#1918](https://github.com/torrentpier/torrentpier/pull/1918)) - ([46f29bc](https://github.com/torrentpier/torrentpier/commit/46f29bc68a18fdefad81e26a60fe44f122407ea7))


## [v2.4.5](https://github.com/torrentpier/torrentpier/compare/v2.4.5-rc.5..v2.4.5) (2025-05-11)

### 🚀 Features

- *(admin_smilies)* Added confirmation on smilie deleting ([#1895](https://github.com/torrentpier/torrentpier/pull/1895)) - ([b51820e](https://github.com/torrentpier/torrentpier/commit/b51820e1861044143321fcde5239c22abc3de984))
- *(announcer)* Check for frozen torrents ([#1770](https://github.com/torrentpier/torrentpier/pull/1770)) - ([6e0786b](https://github.com/torrentpier/torrentpier/commit/6e0786bdee8f1a2557f9ac1dc628983bcafe3f5f))
- *(freeipapi)* Added ability to use own API token ([#1901](https://github.com/torrentpier/torrentpier/pull/1901)) - ([513e306](https://github.com/torrentpier/torrentpier/commit/513e3065d34409931c4198c03b080f232f1d809b))
- Added ability to hide peer username in peer list ([#1903](https://github.com/torrentpier/torrentpier/pull/1903)) - ([3a64f85](https://github.com/torrentpier/torrentpier/commit/3a64f8595cafd99b9cb821d52ec5d3b3e8e467c0))
- Added ability to hide peer country in peer list ([#1891](https://github.com/torrentpier/torrentpier/pull/1891)) - ([2555ebc](https://github.com/torrentpier/torrentpier/commit/2555ebce4717f871922495e48cbca9e22da78bd5))
- Added ability to hide BitTorrent client in peers list ([#1890](https://github.com/torrentpier/torrentpier/pull/1890)) - ([f5d65b8](https://github.com/torrentpier/torrentpier/commit/f5d65b8911c5e864f000348a6d1aefbb4c09c2b4))

### 🐛 Bug Fixes

- *(peers list)* `IPv6` showing ([#1902](https://github.com/torrentpier/torrentpier/pull/1902)) - ([4b7203f](https://github.com/torrentpier/torrentpier/commit/4b7203f8aeeeffc1b163bd3db1dd6b2cac33c923))
- Incorrect rounding in execution time counter ([#1899](https://github.com/torrentpier/torrentpier/pull/1899)) - ([781b724](https://github.com/torrentpier/torrentpier/commit/781b7240c41ddd141cfb057480c10d9cee30e6d7))
- `Undefined array key "smile"` when are no smilies ([#1896](https://github.com/torrentpier/torrentpier/pull/1896)) - ([36d3992](https://github.com/torrentpier/torrentpier/commit/36d399220e2c16a582e1e400df0002c164f5ec3b))
- Peer country flag not shown in peers list ([#1894](https://github.com/torrentpier/torrentpier/pull/1894)) - ([8edba72](https://github.com/torrentpier/torrentpier/commit/8edba72f09f037225ede058cf09c830b1a01e78f))

### 📦 Dependencies

- *(deps)* Bump symfony/polyfill from 1.31.0 to 1.32.0 ([#1900](https://github.com/torrentpier/torrentpier/pull/1900)) - ([a4793f6](https://github.com/torrentpier/torrentpier/commit/a4793f6ce103f22941d72793e2bf8cdf9f78d494))

### ⚙️ Miscellaneous

- Minor improvements ([#1904](https://github.com/torrentpier/torrentpier/pull/1904)) - ([3cdf843](https://github.com/torrentpier/torrentpier/commit/3cdf843a0442d4cdf9b70702c6092d05df86c7e0))
- Minor improvements ([#1898](https://github.com/torrentpier/torrentpier/pull/1898)) - ([2f02692](https://github.com/torrentpier/torrentpier/commit/2f026921ee331226900b3cd4f1bb238f6562b48d))
- Minor improvements ([#1897](https://github.com/torrentpier/torrentpier/pull/1897)) - ([14086a0](https://github.com/torrentpier/torrentpier/commit/14086a0ed6181a0ff4496ee2e56f4fb70bfe18d5))
- Minor improvements ([#1893](https://github.com/torrentpier/torrentpier/pull/1893)) - ([90ece5c](https://github.com/torrentpier/torrentpier/commit/90ece5c7621789f170246b2898841b347e264674))
- Minor improvements ([#1892](https://github.com/torrentpier/torrentpier/pull/1892)) - ([1e5b93d](https://github.com/torrentpier/torrentpier/commit/1e5b93d2c072c5c35feef7567b3fcdb4b3597935))


## [v2.4.5-rc.5](https://github.com/torrentpier/torrentpier/compare/v2.4.5-rc.4..v2.4.5-rc.5) (2025-05-03)

### 🚀 Features

- *(admin_ranks)* Added confirmation on rank deleting ([#1888](https://github.com/torrentpier/torrentpier/pull/1888)) - ([e510ebc](https://github.com/torrentpier/torrentpier/commit/e510ebc3ba30be7bf99769b1e5540353bd53c333))
- *(atom)* Hide topics from private forums ([#1889](https://github.com/torrentpier/torrentpier/pull/1889)) - ([75e9d5e](https://github.com/torrentpier/torrentpier/commit/75e9d5e4a8c5ec20f438e7b24a5469d219959a8c))
- *(avatar upload)* Added `accept="image/*"` attribute ([#1841](https://github.com/torrentpier/torrentpier/pull/1841)) - ([56d531a](https://github.com/torrentpier/torrentpier/commit/56d531aa5ddb778d08a2796fa9fb865e5b3040ce))
- *(emailer)* Added ability to configure `sendmail` - ([5ad4a70](https://github.com/torrentpier/torrentpier/commit/5ad4a7019d996d468650ab608ab53d6cf3ebb4f5))
- *(magnet)* Added `xl` (eXact Length) parametr ([#1883](https://github.com/torrentpier/torrentpier/pull/1883)) - ([c0cdcff](https://github.com/torrentpier/torrentpier/commit/c0cdcff48825ce5fb0c89c0ec44eb95686aee74c))
- *(playback_m3u.php)* Added checking auth to download ([#1848](https://github.com/torrentpier/torrentpier/pull/1848)) - ([0b8d8a5](https://github.com/torrentpier/torrentpier/commit/0b8d8a5210ee761dddaa57fc48bb48b0ede1ec3c))

### 🐛 Bug Fixes

- *(cache)* Implicitly marking parameter `$name` as nullable is deprecated ([#1877](https://github.com/torrentpier/torrentpier/pull/1877)) - ([c3b4000](https://github.com/torrentpier/torrentpier/commit/c3b40003b778a725e958cebee6446bcfd6a68b10))
- Displaying `Network news` and `Latest news` for guests when foums are private ([#1879](https://github.com/torrentpier/torrentpier/pull/1879)) - ([9f96090](https://github.com/torrentpier/torrentpier/commit/9f96090cc419f828e54e69a91a906a3f3d92c255))
- Pagination issue in `Report on action` page ([#1872](https://github.com/torrentpier/torrentpier/pull/1872)) - ([8358aa0](https://github.com/torrentpier/torrentpier/commit/8358aa00de2ec9efd4c51b8bef11bd700a56c19c))
- `tablesorting` issues & incorrect `user_role` for pending users ([#1871](https://github.com/torrentpier/torrentpier/pull/1871)) - ([595adbe](https://github.com/torrentpier/torrentpier/commit/595adbe4da5296b0f3ebde6628e58e878c0fb7d5))
- Fixed TorrentPier build-in emojis showing in ACP ([#1870](https://github.com/torrentpier/torrentpier/pull/1870)) - ([12792e7](https://github.com/torrentpier/torrentpier/commit/12792e74f71a57448277dda46471563a7fea71db))

### 📦 Dependencies

- *(deps)* Bump vlucas/phpdotenv from 5.6.1 to 5.6.2 ([#1887](https://github.com/torrentpier/torrentpier/pull/1887)) - ([7a14464](https://github.com/torrentpier/torrentpier/commit/7a14464d20fe8d2f8b980a82647c6b9ec081f621))
- *(deps)* Bump php-curl-class/php-curl-class from 11.1.0 to 12.0.0 ([#1868](https://github.com/torrentpier/torrentpier/pull/1868)) - ([bd5aa2a](https://github.com/torrentpier/torrentpier/commit/bd5aa2a5e71560409bc630ea2334e33c77458ab3))
- *(deps)* Bump monolog/monolog from 3.8.1 to 3.9.0 ([#1865](https://github.com/torrentpier/torrentpier/pull/1865)) - ([6440162](https://github.com/torrentpier/torrentpier/commit/64401621879af0cc445c38687c571d2fec184410))
- *(deps)* Bump php-curl-class/php-curl-class from 11.0.5 to 11.1.0 ([#1864](https://github.com/torrentpier/torrentpier/pull/1864)) - ([de2fcea](https://github.com/torrentpier/torrentpier/commit/de2fceabedefd07441ba6801417157a9828e0e2a))
- *(deps)* Bump egulias/email-validator from 4.0.3 to 4.0.4 ([#1858](https://github.com/torrentpier/torrentpier/pull/1858)) - ([3ced460](https://github.com/torrentpier/torrentpier/commit/3ced460640e4bfe27a91acd0408e73c3c49e1534))
- *(deps)* Bump filp/whoops from 2.17.0 to 2.18.0 ([#1853](https://github.com/torrentpier/torrentpier/pull/1853)) - ([7ca0582](https://github.com/torrentpier/torrentpier/commit/7ca058256186b7b690003308d660a3a6271e84d2))
- *(deps)* Bump php-curl-class/php-curl-class from 11.0.4 to 11.0.5 ([#1849](https://github.com/torrentpier/torrentpier/pull/1849)) - ([37ad07a](https://github.com/torrentpier/torrentpier/commit/37ad07a40c1adf29f712f469d2850753d32a5eb9))
- *(deps)* Bump belomaxorka/captcha from 1.2.3 to 1.2.4 - ([4641b0a](https://github.com/torrentpier/torrentpier/commit/4641b0a0d0e055d684ec36d41bfaf22b4d4b2ee1))
- *(deps)* Bump belomaxorka/captcha from 1.2.2 to 1.2.3 ([#1842](https://github.com/torrentpier/torrentpier/pull/1842)) - ([be65f7c](https://github.com/torrentpier/torrentpier/commit/be65f7c55cbf81d889d5083c9344ccef400e8e19))

### 🚜 Refactor

- Password generation ([#1847](https://github.com/torrentpier/torrentpier/pull/1847)) - ([af2403f](https://github.com/torrentpier/torrentpier/commit/af2403f1918845e8af3d9fa7708623eef6aa427e))
- Moved `Select` class into `Legacy\Common` ([#1846](https://github.com/torrentpier/torrentpier/pull/1846)) - ([bd0ef06](https://github.com/torrentpier/torrentpier/commit/bd0ef063fac328ed16537aacbc12e287a8d8206b))

### ⚙️ Miscellaneous

- *(.cliffignore)* Added one more commit ([#1860](https://github.com/torrentpier/torrentpier/pull/1860)) - ([974d359](https://github.com/torrentpier/torrentpier/commit/974d3590c1fb11c6314da4a4b8115a2229e32bbd))
- *(README)* Removed `Build actions` badge ([#1861](https://github.com/torrentpier/torrentpier/pull/1861)) - ([e9920ab](https://github.com/torrentpier/torrentpier/commit/e9920ab59803552e3a1a00b603962208a62efe4e))
- *(cliff)* Added `.cliffignore` file to ignore reverted commits ([#1859](https://github.com/torrentpier/torrentpier/pull/1859)) - ([2eab551](https://github.com/torrentpier/torrentpier/commit/2eab551bd75e7acfd6f4dabe13b2a30ac09db880))
- *(nightly builds)* Added cleanup step ([#1851](https://github.com/torrentpier/torrentpier/pull/1851)) - ([299d9a1](https://github.com/torrentpier/torrentpier/commit/299d9a1f6c4f244e435803212e763c252e5bd396))
- *(password_hash)* Changed `cost` to `12` by default ([#1886](https://github.com/torrentpier/torrentpier/pull/1886)) - ([1663e19](https://github.com/torrentpier/torrentpier/commit/1663e19c3f80ae15792d6ffe4ce64e40129b14db))
- *(render_flag)* Hide names for specified (`$nameIgnoreList`) flags ([#1862](https://github.com/torrentpier/torrentpier/pull/1862)) - ([83e42bc](https://github.com/torrentpier/torrentpier/commit/83e42bc5db086f60a6038b3fffca5982ceeced51))
- *(text captcha)* Disabled scatter effect by default - ([3af5202](https://github.com/torrentpier/torrentpier/commit/3af5202f7b2a4ea5d14bbc4808b7a380de2e0dc0))
- Updated nightly builds link ([#1885](https://github.com/torrentpier/torrentpier/pull/1885)) - ([6bd000b](https://github.com/torrentpier/torrentpier/commit/6bd000bc0d6176dbe1f0a573f081c9daefd3718b))
- Composer dependencies are installed according to the minimum supported PHP version ([#1884](https://github.com/torrentpier/torrentpier/pull/1884)) - ([5fe7700](https://github.com/torrentpier/torrentpier/commit/5fe770070e1cd71ea50ea3ad3825a322774f0baf))
- Corrected `php` version in `composer.json` ([#1882](https://github.com/torrentpier/torrentpier/pull/1882)) - ([bc1713a](https://github.com/torrentpier/torrentpier/commit/bc1713abdd28d04e8e1da3c3eabeb5170a35a460))
- Composer dependencies are installed according to the minimum supported PHP version ([#1881](https://github.com/torrentpier/torrentpier/pull/1881)) - ([5c4972e](https://github.com/torrentpier/torrentpier/commit/5c4972ec12340cbffb8ac941d390ee6c2c89b635))
- Minor improvements ([#1880](https://github.com/torrentpier/torrentpier/pull/1880)) - ([de8f192](https://github.com/torrentpier/torrentpier/commit/de8f1925bae3b38db18b86eb4a10337853638ad7))
- Minor improvements ([#1876](https://github.com/torrentpier/torrentpier/pull/1876)) - ([eeb391d](https://github.com/torrentpier/torrentpier/commit/eeb391da6a16440492a3b803f63be301ba3d02d3))
- Minor improvements ([#1875](https://github.com/torrentpier/torrentpier/pull/1875)) - ([41a78dd](https://github.com/torrentpier/torrentpier/commit/41a78ddbcbc628f0592c59879df0170bf48664aa))
- Minor improvements ([#1874](https://github.com/torrentpier/torrentpier/pull/1874)) - ([0f1a69e](https://github.com/torrentpier/torrentpier/commit/0f1a69e32d8d5eb5053b021844845911c619d8cd))
- Fetch only necessary sitemap parameters in `admin_sitemap.php` ([#1873](https://github.com/torrentpier/torrentpier/pull/1873)) - ([f9c8160](https://github.com/torrentpier/torrentpier/commit/f9c8160f8e897950a038a74ad7ee30b116f7b2b8))
- Changed placeholder IP address from `7f000001` to `0` ([#1869](https://github.com/torrentpier/torrentpier/pull/1869)) - ([84e2392](https://github.com/torrentpier/torrentpier/commit/84e23928968f943826bdc4390c52365357d56f32))
- Minor improvements ([#1866](https://github.com/torrentpier/torrentpier/pull/1866)) - ([7237653](https://github.com/torrentpier/torrentpier/commit/72376532b32395eda04dc032c07ca08b27346c6b))
- Some minor improvements ([#1855](https://github.com/torrentpier/torrentpier/pull/1855)) - ([3cc880e](https://github.com/torrentpier/torrentpier/commit/3cc880eeb8be41596d5e8eaf19297046500afcf7))

### ◀️ Revert

- Added `TorrentPier instance hash` generation - ([eabf851](https://github.com/torrentpier/torrentpier/commit/eabf851ee60d29835d1979f46dcf2b9d82576c1b))
- Added `IndexNow` protocol support 🤖 - ([1b288a9](https://github.com/torrentpier/torrentpier/commit/1b288a96e443e06c4f4e9ea374037d3b0af8a639))


## [v2.4.5-rc.4](https://github.com/torrentpier/torrentpier/compare/v2.4.5-rc.3..v2.4.5-rc.4) (2025-03-09)

### 🚀 Features

- *(captcha)* Added `Text Captcha` provider ([#1839](https://github.com/torrentpier/torrentpier/pull/1839)) - ([74ea157](https://github.com/torrentpier/torrentpier/commit/74ea1573b298be5a935caaca0b3cc57cb1e9264a))
- *(show post bbcode)* Added `'only_for_first_post'` param ([#1830](https://github.com/torrentpier/torrentpier/pull/1830)) - ([4dcd1fb](https://github.com/torrentpier/torrentpier/commit/4dcd1fb16e4e84acd1231ad821a2f05658b849ad))
- *(sitemap)* Update `lastmod` when a new reply in topic ([#1737](https://github.com/torrentpier/torrentpier/pull/1737)) - ([bc95e14](https://github.com/torrentpier/torrentpier/commit/bc95e14be328303bb37e31299661b03045e37d07))
- Added `$bb_cfg['auto_language_detection']` parametr ([#1835](https://github.com/torrentpier/torrentpier/pull/1835)) - ([b550fa5](https://github.com/torrentpier/torrentpier/commit/b550fa59f9ee96ca89e5b6db880147bc72841e93))
- Easter egg for the 20th anniversary of the TorrentPier! ([#1831](https://github.com/torrentpier/torrentpier/pull/1831)) - ([f2e513d](https://github.com/torrentpier/torrentpier/commit/f2e513dd8b0f82f4f02474db4b83d83904a93f29))
- Added configuration files for `nginx` & `caddy` ([#1787](https://github.com/torrentpier/torrentpier/pull/1787)) - ([f7d3946](https://github.com/torrentpier/torrentpier/commit/f7d394607e4ea5bb9b7f2b33692204a226a4d78b))

### 🐛 Bug Fixes

- *(info.php)* Undefined array key "show" ([#1836](https://github.com/torrentpier/torrentpier/pull/1836)) - ([f8c4e8f](https://github.com/torrentpier/torrentpier/commit/f8c4e8fb14090bc7403f24e363603bad9e231351))
- *(tr_seed_bonus.php)* Incorrect `GROUP BY` ([#1820](https://github.com/torrentpier/torrentpier/pull/1820)) - ([dfd4e5e](https://github.com/torrentpier/torrentpier/commit/dfd4e5ebc9df916868210a7844f2a6f35e7b8aca))

### 📦 Dependencies

- *(deps)* Bump bugsnag/bugsnag from 3.29.2 to 3.29.3 ([#1837](https://github.com/torrentpier/torrentpier/pull/1837)) - ([b954815](https://github.com/torrentpier/torrentpier/commit/b954815f5d0dce9520f65679e834d8bd49e571e0))
- *(deps)* Bump php-curl-class/php-curl-class from 11.0.3 to 11.0.4 ([#1823](https://github.com/torrentpier/torrentpier/pull/1823)) - ([1c323a4](https://github.com/torrentpier/torrentpier/commit/1c323a45d777b033155da9a2becec506215bd94c))
- *(deps)* Bump php-curl-class/php-curl-class from 11.0.1 to 11.0.3 ([#1821](https://github.com/torrentpier/torrentpier/pull/1821)) - ([dedf35b](https://github.com/torrentpier/torrentpier/commit/dedf35b794196034eb27d4125dff0798aed5f315))

### 🗑️ Removed

- *(posting.php)* Unused `'U_VIEWTOPIC` variable ([#1818](https://github.com/torrentpier/torrentpier/pull/1818)) - ([03ebbda](https://github.com/torrentpier/torrentpier/commit/03ebbda6be567d82d2a49fefe02356544fbd07cb))
- Integrity checker 🥺🪦 ([#1827](https://github.com/torrentpier/torrentpier/pull/1827)) - ([ba3ce88](https://github.com/torrentpier/torrentpier/commit/ba3ce885c8d84ae939a0ce9c79b97877d3aaab41))
- Redundant `.htaccess` files ([#1826](https://github.com/torrentpier/torrentpier/pull/1826)) - ([912b080](https://github.com/torrentpier/torrentpier/commit/912b080b16438b09f82fbc72a363589cc2f6209e))

### ⚙️ Miscellaneous

- *(Caddyfile)* Some minor fixes ([#1822](https://github.com/torrentpier/torrentpier/pull/1822)) - ([6f641aa](https://github.com/torrentpier/torrentpier/commit/6f641aa9d8d7afb30920c054a43347393ea05cc4))
- *(README)* Fixed all grammatical errors, sentence structure and readibility ([#1812](https://github.com/torrentpier/torrentpier/pull/1812)) - ([bea3b0b](https://github.com/torrentpier/torrentpier/commit/bea3b0bccf335970ea5826543d8fa223329ef077))
- *(_cleanup.php)* Added CLI mode check ([#1834](https://github.com/torrentpier/torrentpier/pull/1834)) - ([5dc9a54](https://github.com/torrentpier/torrentpier/commit/5dc9a5475c051911c579ea732ef52d7feb78e8ac))
- *(announcer)* Some minor improvements ([#1819](https://github.com/torrentpier/torrentpier/pull/1819)) - ([bdefed4](https://github.com/torrentpier/torrentpier/commit/bdefed4dab3cc65330fcb9cb9750cc8e84beda1d))
- *(cliff)* Removed TorrentPier logo ([#1817](https://github.com/torrentpier/torrentpier/pull/1817)) - ([7794242](https://github.com/torrentpier/torrentpier/commit/7794242750b44183312a2a45c9f54c6afde12f0e))
- *(cliff)* Synced `cliff-releases.toml` with `cliff.toml` changes ([#1815](https://github.com/torrentpier/torrentpier/pull/1815)) - ([f2aea92](https://github.com/torrentpier/torrentpier/commit/f2aea92b3d79d72254e696fde31ad9b4bec5dcd0))
- *(cliff)* Added missing line breaks after `body` ([#1814](https://github.com/torrentpier/torrentpier/pull/1814)) - ([2593f09](https://github.com/torrentpier/torrentpier/commit/2593f093a389a9c450725290862b99d911fbef5d))
- *(installer)* Added cleanup step (for master builds) ([#1838](https://github.com/torrentpier/torrentpier/pull/1838)) - ([dd72136](https://github.com/torrentpier/torrentpier/commit/dd721367c7dc9956861fcd33af7f9f822cf80011))
- *(installer)* Some minor improvements ([#1825](https://github.com/torrentpier/torrentpier/pull/1825)) - ([4f89685](https://github.com/torrentpier/torrentpier/commit/4f896854d3bb67300027f7542704f41c4869837f))
- *(installer)* Some minor improvements ([#1824](https://github.com/torrentpier/torrentpier/pull/1824)) - ([f3714f0](https://github.com/torrentpier/torrentpier/commit/f3714f02f2c8fbfaccfdafb8f25a269664c48950))
- *(workflow)* Short `release_name` ([#1816](https://github.com/torrentpier/torrentpier/pull/1816)) - ([c57db21](https://github.com/torrentpier/torrentpier/commit/c57db2104d7b8363d0b8ce8872ce90fc7410c724))
- *(workflow)* Added `workflow_dispatch` for  `schedule.yml` ([#1813](https://github.com/torrentpier/torrentpier/pull/1813)) - ([d54c07b](https://github.com/torrentpier/torrentpier/commit/d54c07b3da00fc8bcba5413cd4ae3f3c9f6007bb))
- *(workflow)* Some improvements ([#1811](https://github.com/torrentpier/torrentpier/pull/1811)) - ([3a9dd6a](https://github.com/torrentpier/torrentpier/commit/3a9dd6a3c931cfbd682257c283a3296c4914548f))
- *(workflow)* Some improvements ([#1810](https://github.com/torrentpier/torrentpier/pull/1810)) - ([c168c39](https://github.com/torrentpier/torrentpier/commit/c168c3956cf77886c14133ac10ec33aa0ae5bc4e))
- Replaced `gregwar/captcha` with my own fork ([#1840](https://github.com/torrentpier/torrentpier/pull/1840)) - ([8585560](https://github.com/torrentpier/torrentpier/commit/858556043d3e45218ea8e803786d6b6de6d485d0))
- Created cleanup script (for releases preparation) ([#1833](https://github.com/torrentpier/torrentpier/pull/1833)) - ([68bf26d](https://github.com/torrentpier/torrentpier/commit/68bf26d0f4ab33f5394d26f425e53817f3464ac8))
- Bring back missing `cache` & `log` directories ([#1832](https://github.com/torrentpier/torrentpier/pull/1832)) - ([249c988](https://github.com/torrentpier/torrentpier/commit/249c9889890291d56317dd703414bdb57ecaa41f))
- Some minor improvements ([#1829](https://github.com/torrentpier/torrentpier/pull/1829)) - ([3b8ee4c](https://github.com/torrentpier/torrentpier/commit/3b8ee4c4d3ab4631425fbe44f197b6a9bd7d158c))

## New Contributors ❤️

* @xeddmc made their first contribution in [#1812](https://github.com/torrentpier/torrentpier/pull/1812)

## [v2.4.5-rc.3](https://github.com/torrentpier/torrentpier/compare/v2.4.5-rc.2..v2.4.5-rc.3) (2025-02-06)

### 🚀 Features

- *(announcer)* Added some disallowed ports by default ([#1767](https://github.com/torrentpier/torrentpier/pull/1767)) - ([46288ec](https://github.com/torrentpier/torrentpier/commit/46288ec19830c84aedb156e1f30d7ec8a0803e0d))
- *(announcer)* Added `is_numeric()` checking for some fields ([#1766](https://github.com/torrentpier/torrentpier/pull/1766)) - ([096bb51](https://github.com/torrentpier/torrentpier/commit/096bb5124fa27d27c3e60031edc432d877f1c507))
- *(announcer)* Added `event` verifying ([#1765](https://github.com/torrentpier/torrentpier/pull/1765)) - ([6a19323](https://github.com/torrentpier/torrentpier/commit/6a1932313801e55fbcfb047fdcef87266f472c33))
- *(announcer)* Block browser by checking the `User-Agent` ([#1764](https://github.com/torrentpier/torrentpier/pull/1764)) - ([7b64b50](https://github.com/torrentpier/torrentpier/commit/7b64b508199af568472fe6ac2edf333a3e274a00))
- *(announcer)* Block `User-Agent` strings that are too long ([#1763](https://github.com/torrentpier/torrentpier/pull/1763)) - ([a98f8f1](https://github.com/torrentpier/torrentpier/commit/a98f8f102a8253b0b22c80ef444fed1ec29177f3))
- *(announcer)* Blocking all ports lower then `1024` ([#1762](https://github.com/torrentpier/torrentpier/pull/1762)) - ([1bc7e09](https://github.com/torrentpier/torrentpier/commit/1bc7e09ddbeaf680b86095eed9a80b8ebf6169b3))
- *(cache)* Checking if extensions are installed ([#1759](https://github.com/torrentpier/torrentpier/pull/1759)) - ([7f31022](https://github.com/torrentpier/torrentpier/commit/7f31022cfca2acb28a5cba06961eeaf8d2c9de51))
- *(captcha)* Added some new services 🤖 ([#1771](https://github.com/torrentpier/torrentpier/pull/1771)) - ([d413c71](https://github.com/torrentpier/torrentpier/commit/d413c717188c9bd906f715e7137955dc9a42a003))
- *(environment)* Make configurable `TP_HOST` and `TP_PORT` ([#1780](https://github.com/torrentpier/torrentpier/pull/1780)) - ([e51e091](https://github.com/torrentpier/torrentpier/commit/e51e09159333382a77b809b5f1da5e152a713143))
- *(installer)* Fully show non-installed extensions ([#1761](https://github.com/torrentpier/torrentpier/pull/1761)) - ([8fcc62d](https://github.com/torrentpier/torrentpier/commit/8fcc62d2a2fd41927b2f5dae215fe5bbf95f2c96))
- *(installer)* More explanations ([#1758](https://github.com/torrentpier/torrentpier/pull/1758)) - ([48ab52a](https://github.com/torrentpier/torrentpier/commit/48ab52ac8674afcb607c8e49134316a3e117236a))
- *(installer)* Check `Composer` dependencies after installing ([#1756](https://github.com/torrentpier/torrentpier/pull/1756)) - ([262b887](https://github.com/torrentpier/torrentpier/commit/262b8872a5b14068eb73d745adea6203c557e192))
- *(installer)* More explanations ([#1754](https://github.com/torrentpier/torrentpier/pull/1754)) - ([fd6f1f8](https://github.com/torrentpier/torrentpier/commit/fd6f1f86a5e9216469cd648601ecb9ba875f9eb6))
- *(installer)* Create `config.local.php` on local environment ([#1745](https://github.com/torrentpier/torrentpier/pull/1745)) - ([0d93b2c](https://github.com/torrentpier/torrentpier/commit/0d93b2c768c2965c12ac62e2f3b2886dc1ef31c2))
- *(torrent)* Bring back old torrent file naming ([#1783](https://github.com/torrentpier/torrentpier/pull/1783)) - ([314c592](https://github.com/torrentpier/torrentpier/commit/314c592affbef4b8db48d562b9633aad27059a76))
- *(workflow)* Automated deploy actual changes to `TorrentPier Demo` ([#1788](https://github.com/torrentpier/torrentpier/pull/1788)) - ([4333d6a](https://github.com/torrentpier/torrentpier/commit/4333d6aca4aeb8584ff8a8ef0bf76c537a3f371a))
- Used `TORRENT_MIMETYPE` constant instead of hardcoded string ([#1757](https://github.com/torrentpier/torrentpier/pull/1757)) - ([4b0d270](https://github.com/torrentpier/torrentpier/commit/4b0d270c89ec06abed590504f6a0cb70076a9e59))

### 🐛 Bug Fixes

- *(announcer)* Null `event` exception ([#1784](https://github.com/torrentpier/torrentpier/pull/1784)) - ([b06e327](https://github.com/torrentpier/torrentpier/commit/b06e327cbb285a676814699eb5fb1fbc0e1f22e8))
- *(bb_die)* HTML characters converting ([#1744](https://github.com/torrentpier/torrentpier/pull/1744)) - ([4f1c7e4](https://github.com/torrentpier/torrentpier/commit/4f1c7e40d82e52f81eba44ead501e1f01058cc4f))
- *(debug)* Disabled `Bugsnag` reporting on local environment ([#1751](https://github.com/torrentpier/torrentpier/pull/1751)) - ([1f3b629](https://github.com/torrentpier/torrentpier/commit/1f3b629e9cea4d11fbf3cf29f575ba730bad898d))
- *(installer)* Missing `gd` extension ([#1749](https://github.com/torrentpier/torrentpier/pull/1749)) - ([a1c519d](https://github.com/torrentpier/torrentpier/commit/a1c519d938b848edffcbf7fbbe6a3fdb9a5394f1))
- *(youtube player)* Mixed content issue ([#1795](https://github.com/torrentpier/torrentpier/pull/1795)) - ([3c0a1d5](https://github.com/torrentpier/torrentpier/commit/3c0a1d5d0018daa87ad3914ea04078a9a6d05fc2))
- Incorrect peer country flag ([#1768](https://github.com/torrentpier/torrentpier/pull/1768)) - ([0f091eb](https://github.com/torrentpier/torrentpier/commit/0f091eb546e34923d9d1ab34be5faf92080ec198))

### 📦 Dependencies

- *(deps)* Bump jacklul/monolog-telegram from 3.1.0 to 3.2.0 ([#1776](https://github.com/torrentpier/torrentpier/pull/1776)) - ([420c92c](https://github.com/torrentpier/torrentpier/commit/420c92c0addf4dee91f3ae872517cb3224827a1f))
- *(deps)* Bump filp/whoops from 2.16.0 to 2.17.0 ([#1777](https://github.com/torrentpier/torrentpier/pull/1777)) - ([a71609b](https://github.com/torrentpier/torrentpier/commit/a71609ba67a89480fabb7b62de450d9be09373fa))
- *(deps)* Bump php-curl-class/php-curl-class from 11.0.0 to 11.0.1 ([#1753](https://github.com/torrentpier/torrentpier/pull/1753)) - ([ce32031](https://github.com/torrentpier/torrentpier/commit/ce32031a0fb14cdf6c3f4ba379b530cbb52b0fea))
- *(deps)* Bump bugsnag/bugsnag from 3.29.1 to 3.29.2 ([#1752](https://github.com/torrentpier/torrentpier/pull/1752)) - ([f63d15c](https://github.com/torrentpier/torrentpier/commit/f63d15c49e3992837413b2c7a0160d599b44f2ef))

### 🗑️ Removed

- *(environment)* Extra `DB_CONNECTION` variable ([#1775](https://github.com/torrentpier/torrentpier/pull/1775)) - ([cd2786b](https://github.com/torrentpier/torrentpier/commit/cd2786bb69c74cec88a447f66750d014fc4d3612))
- Some unused tracker config variables ([#1769](https://github.com/torrentpier/torrentpier/pull/1769)) - ([7f9df35](https://github.com/torrentpier/torrentpier/commit/7f9df35d3bd0e9d23284b8bd9c36a0f52158f5d7))

### 📚 Documentation

- Minor improvements ([#1750](https://github.com/torrentpier/torrentpier/pull/1750)) - ([3e850ac](https://github.com/torrentpier/torrentpier/commit/3e850ac724c43e813aa077b272b498e2b0477260))

### ⚙️ Miscellaneous

- *(cd workflow)* Fixed release body creation ([#1809](https://github.com/torrentpier/torrentpier/pull/1809)) - ([7378cb3](https://github.com/torrentpier/torrentpier/commit/7378cb3af5cc56343c667a9d920038b05327e97b))
- *(cd workflow)* Fixed release body creation ([#1807](https://github.com/torrentpier/torrentpier/pull/1807)) - ([cc679a8](https://github.com/torrentpier/torrentpier/commit/cc679a80246f3ff65136653025d826bf1458db3a))
- *(changelog workflow)* Minor improvements ([#1802](https://github.com/torrentpier/torrentpier/pull/1802)) - ([15ca21f](https://github.com/torrentpier/torrentpier/commit/15ca21f03840281f7d4402959aa8bfb7d407b45b))
- *(checksum workflow)* Fixed incorrect file path ([#1799](https://github.com/torrentpier/torrentpier/pull/1799)) - ([4eb5a9a](https://github.com/torrentpier/torrentpier/commit/4eb5a9adc61c4e116feb09208091efb914275da2))
- *(cliff)* Changed emoji for dependencies ([#1755](https://github.com/torrentpier/torrentpier/pull/1755)) - ([55d4670](https://github.com/torrentpier/torrentpier/commit/55d467048370b51cd592982c8026702dca8813d5))
- *(cliff)* Use blockquote for notice ([#1748](https://github.com/torrentpier/torrentpier/pull/1748)) - ([61e5592](https://github.com/torrentpier/torrentpier/commit/61e55925f312417bdb63c88a7c8939c3b2eb2ac5))
- *(cliff)* Fixed typo ([#1747](https://github.com/torrentpier/torrentpier/pull/1747)) - ([4936af7](https://github.com/torrentpier/torrentpier/commit/4936af7d3d10f553d8586a14de249c32e50f3494))
- *(cliff)* Notice about previous changelog file ([#1746](https://github.com/torrentpier/torrentpier/pull/1746)) - ([85395be](https://github.com/torrentpier/torrentpier/commit/85395be5e7c6a891c79ec72cf215894af097f819))
- *(copyright)* Updated copyright year ([#1760](https://github.com/torrentpier/torrentpier/pull/1760)) - ([6697410](https://github.com/torrentpier/torrentpier/commit/6697410c1df6c8d9d7f511b1e984ae90d888ae0e))
- *(database)* Use `DEFAULT ''` for `privmsgs_subject` ([#1786](https://github.com/torrentpier/torrentpier/pull/1786)) - ([387a258](https://github.com/torrentpier/torrentpier/commit/387a25870abd37b641b55ffd98e13f4aaecb73b1))
- *(deploy action)* Specify some missing params ([#1789](https://github.com/torrentpier/torrentpier/pull/1789)) - ([6115900](https://github.com/torrentpier/torrentpier/commit/6115900b765752209a6ed1dfb83e4f0cbee2ae77))
- *(emailer)* Use constants for email types ([#1794](https://github.com/torrentpier/torrentpier/pull/1794)) - ([c95d414](https://github.com/torrentpier/torrentpier/commit/c95d414ef63ca37118f1f660880cd58b4480c414))
- *(integrity checker)* Disabled by default in `Demo mode` ([#1804](https://github.com/torrentpier/torrentpier/pull/1804)) - ([44be40c](https://github.com/torrentpier/torrentpier/commit/44be40c2e849c60eb4f10ca7e0bae0463791355e))
- *(integrity checker)* Some enhancements ([#1797](https://github.com/torrentpier/torrentpier/pull/1797)) - ([09cafc2](https://github.com/torrentpier/torrentpier/commit/09cafc2285dd171cb2213ece9549993a3321527c))
- *(issue template)* Improved `Feature request` template ([#1774](https://github.com/torrentpier/torrentpier/pull/1774)) - ([268f79d](https://github.com/torrentpier/torrentpier/commit/268f79d7259de67aa8877fcf7130ff0069469ab2))
- *(issue template)* Improved `Bug report` template ([#1773](https://github.com/torrentpier/torrentpier/pull/1773)) - ([53ebfef](https://github.com/torrentpier/torrentpier/commit/53ebfef32c0e9016257e03b96ef96349e22d3e9b))
- *(notify)* Hide notify checkbox in topic for guests ([#1793](https://github.com/torrentpier/torrentpier/pull/1793)) - ([8e4cd97](https://github.com/torrentpier/torrentpier/commit/8e4cd97734fc46f33459c4b00a0fe38b0597f92b))
- *(readme)* Improved installation guide ([#1781](https://github.com/torrentpier/torrentpier/pull/1781)) - ([e579b81](https://github.com/torrentpier/torrentpier/commit/e579b816b4dc346b3242cb3d9db292ad05596c1f))
- *(readme)* Minor improvements ([#1779](https://github.com/torrentpier/torrentpier/pull/1779)) - ([5b0ed02](https://github.com/torrentpier/torrentpier/commit/5b0ed020890a8f938df912f9215cccbda42b0317))
- *(readme)* Added Caddy webserver ([#1778](https://github.com/torrentpier/torrentpier/pull/1778)) - ([970a028](https://github.com/torrentpier/torrentpier/commit/970a0282e3631c403029c959ffd46b21c5cad0cd))
- *(workflow)* Refactored all workflows ([#1803](https://github.com/torrentpier/torrentpier/pull/1803)) - ([a29d57b](https://github.com/torrentpier/torrentpier/commit/a29d57b2f8673733bbfbea3fb96eebe841078d49))
- *(workflow)* Trying combine `changelog workflow` with `checksums workflow` ([#1800](https://github.com/torrentpier/torrentpier/pull/1800)) - ([60c6057](https://github.com/torrentpier/torrentpier/commit/60c605778412335ce97d41489c3b6ee9c051454b))
- Automated releases generation ([#1808](https://github.com/torrentpier/torrentpier/pull/1808)) - ([6c9372c](https://github.com/torrentpier/torrentpier/commit/6c9372c407327c9bb443b2ecf16eff64c0245c4b))
- Automated releases generation ([#1806](https://github.com/torrentpier/torrentpier/pull/1806)) - ([bc74550](https://github.com/torrentpier/torrentpier/commit/bc745502940207f3f24c83057cd680fe69355961))
- Automated releases generation ([#1805](https://github.com/torrentpier/torrentpier/pull/1805)) - ([425e2e8](https://github.com/torrentpier/torrentpier/commit/425e2e87d5a7f097b961b1a14fbafcdabb9d1666))
- Minor improvements ([#1796](https://github.com/torrentpier/torrentpier/pull/1796)) - ([8650ad3](https://github.com/torrentpier/torrentpier/commit/8650ad30f429ab14a03f44b26d7be7701f1985f1))
- Update `cliff.toml` - ([254dca2](https://github.com/torrentpier/torrentpier/commit/254dca2b27c2d92421d3e639c80b0adf1172202f))
- Minor improvements ([#1743](https://github.com/torrentpier/torrentpier/pull/1743)) - ([e73d650](https://github.com/torrentpier/torrentpier/commit/e73d65011fff0a8b8e1368eef61bbfb67e87eab8))
- Enabled `$bb_cfg['integrity_check']` by defaul ([#1742](https://github.com/torrentpier/torrentpier/pull/1742)) - ([7e3601e](https://github.com/torrentpier/torrentpier/commit/7e3601e63aff73be1428969ca37dda3da2537d9b))

## New Contributors ❤️

* @actions-user made their first contribution


