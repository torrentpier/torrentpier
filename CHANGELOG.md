[![TorrentPier](https://raw.githubusercontent.com/torrentpier/.github/refs/heads/main/versions/Cattle.png)](https://github.com/torrentpier)

# 📖 Change Log

## [nightly](https://nightly.link/torrentpier/torrentpier/workflows/ci/master/TorrentPier-master)

### 🚀 Features

- *(database)* Add visual markers for Nette Explorer queries in debug panel ([#1965](https://github.com/torrentpier/torrentpier/pull/1965)) - ([2fd3067](https://github.com/torrentpier/torrentpier/commit/2fd306704f21febee7d53f4b4531601ce0cb81ce))
- *(migrations)* Implement Phinx database migration system ([#1976](https://github.com/torrentpier/torrentpier/pull/1976)) - ([fbde8cd](https://github.com/torrentpier/torrentpier/commit/fbde8cd421c9048afe70ddb41d0a9ed26d3fbef5))
- [**breaking**] Implement Language singleton with shorthand functions ([#1966](https://github.com/torrentpier/torrentpier/pull/1966)) - ([49717d3](https://github.com/torrentpier/torrentpier/commit/49717d3a687b95885fe9773f2597354aed4b2b60))

### 🚜 Refactor

- *(database)* Rename DB to Database and extract debug functionality ([#1964](https://github.com/torrentpier/torrentpier/pull/1964)) - ([6c0219d](https://github.com/torrentpier/torrentpier/commit/6c0219d53c7544b7d8a6374c0d0848945d32ae17))


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



