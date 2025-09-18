[![TorrentPier](https://raw.githubusercontent.com/torrentpier/.github/refs/heads/main/versions/Cattle.png)](https://github.com/torrentpier)

# 📖 Change Log

## [v2.8.5](https://github.com/torrentpier/torrentpier/compare/v2.8.4.1..v2.8.5) (2025-09-18)

### 🐛 Bug Fixes

- *(PHP 8.4)* Replace `trigger_error` with `RuntimeException` ([#2142](https://github.com/torrentpier/torrentpier/pull/2142)) - ([4112eb9](https://github.com/torrentpier/torrentpier/commit/4112eb99c0b65915c70bdb8e94a12f5e4a3baf0d))
- *(posting.php)* Use `auth_mod` instead of `IS_AM` when checking robots indexing ([#2140](https://github.com/torrentpier/torrentpier/pull/2140)) - ([4f91777](https://github.com/torrentpier/torrentpier/commit/4f91777a9b625a16bfd46e93a04d4c260462a22f))
- Use `development` environment instead of `local` ([#2143](https://github.com/torrentpier/torrentpier/pull/2143)) - ([9b6ba59](https://github.com/torrentpier/torrentpier/commit/9b6ba595a412fe5c0b3ea37c7ff87587c13b4e77))
- Passkey not showing when user ratio disabled ([#2141](https://github.com/torrentpier/torrentpier/pull/2141)) - ([9b5aee5](https://github.com/torrentpier/torrentpier/commit/9b5aee59cd49347ff1fda4b6d396ce02656f1037))

### 🈳 New translations

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

### 🈳 New translations

- New Crowdin updates ([#2127](https://github.com/torrentpier/torrentpier/pull/2127)) - ([1495a75](https://github.com/torrentpier/torrentpier/commit/1495a754825252cdcb372448ae3aa5b6b04604e6))

### 📦 Dependencies

- Replace `belomaxorka/captcha` with `gregwar/captcha` ([#2069](https://github.com/torrentpier/torrentpier/pull/2069)) - ([656f1ae](https://github.com/torrentpier/torrentpier/commit/656f1ae81689aa6d7a11807fba4c84111f6cec86))

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



