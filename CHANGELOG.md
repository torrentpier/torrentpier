# 📖 Change Log

> [!NOTE]
> To view changelog from **v2.0.0** to **v2.4.5-rc.2** navigate to [HISTORY.md](HISTORY.md).
## [nightly](https://nightly.link/torrentpier/torrentpier/workflows/build/master/TorrentPier)

### 🚀 Features

- *(announcer)* Added `is_numeric()` checking for some fields ([#1766](https://github.com/torrentpier/torrentpier/pull/1766)) - ([096bb51](https://github.com/torrentpier/torrentpier/commit/096bb5124fa27d27c3e60031edc432d877f1c507))
- *(announcer)* Added `event` verifying ([#1765](https://github.com/torrentpier/torrentpier/pull/1765)) - ([6a19323](https://github.com/torrentpier/torrentpier/commit/6a1932313801e55fbcfb047fdcef87266f472c33))
- *(announcer)* Block browser by checking the `User-Agent` ([#1764](https://github.com/torrentpier/torrentpier/pull/1764)) - ([7b64b50](https://github.com/torrentpier/torrentpier/commit/7b64b508199af568472fe6ac2edf333a3e274a00))
- *(announcer)* Block `User-Agent` strings that are too long ([#1763](https://github.com/torrentpier/torrentpier/pull/1763)) - ([a98f8f1](https://github.com/torrentpier/torrentpier/commit/a98f8f102a8253b0b22c80ef444fed1ec29177f3))
- *(announcer)* Blocking all ports lower then `1024` ([#1762](https://github.com/torrentpier/torrentpier/pull/1762)) - ([1bc7e09](https://github.com/torrentpier/torrentpier/commit/1bc7e09ddbeaf680b86095eed9a80b8ebf6169b3))
- *(cache)* Checking if extensions are installed ([#1759](https://github.com/torrentpier/torrentpier/pull/1759)) - ([7f31022](https://github.com/torrentpier/torrentpier/commit/7f31022cfca2acb28a5cba06961eeaf8d2c9de51))
- *(installer)* Fully show non-installed extensions ([#1761](https://github.com/torrentpier/torrentpier/pull/1761)) - ([8fcc62d](https://github.com/torrentpier/torrentpier/commit/8fcc62d2a2fd41927b2f5dae215fe5bbf95f2c96))
- *(installer)* More explanations ([#1758](https://github.com/torrentpier/torrentpier/pull/1758)) - ([48ab52a](https://github.com/torrentpier/torrentpier/commit/48ab52ac8674afcb607c8e49134316a3e117236a))
- *(installer)* Check `Composer` dependencies after installing ([#1756](https://github.com/torrentpier/torrentpier/pull/1756)) - ([262b887](https://github.com/torrentpier/torrentpier/commit/262b8872a5b14068eb73d745adea6203c557e192))
- *(installer)* More explanations ([#1754](https://github.com/torrentpier/torrentpier/pull/1754)) - ([fd6f1f8](https://github.com/torrentpier/torrentpier/commit/fd6f1f86a5e9216469cd648601ecb9ba875f9eb6))
- *(installer)* Create `config.local.php` on local environment ([#1745](https://github.com/torrentpier/torrentpier/pull/1745)) - ([0d93b2c](https://github.com/torrentpier/torrentpier/commit/0d93b2c768c2965c12ac62e2f3b2886dc1ef31c2))
- Used `TORRENT_MIMETYPE` constant instead of hardcoded string ([#1757](https://github.com/torrentpier/torrentpier/pull/1757)) - ([4b0d270](https://github.com/torrentpier/torrentpier/commit/4b0d270c89ec06abed590504f6a0cb70076a9e59))

### 🐛 Bug Fixes

- *(bb_die)* HTML characters converting ([#1744](https://github.com/torrentpier/torrentpier/pull/1744)) - ([4f1c7e4](https://github.com/torrentpier/torrentpier/commit/4f1c7e40d82e52f81eba44ead501e1f01058cc4f))
- *(debug)* Disabled `Bugsnag` reporting on local environment ([#1751](https://github.com/torrentpier/torrentpier/pull/1751)) - ([1f3b629](https://github.com/torrentpier/torrentpier/commit/1f3b629e9cea4d11fbf3cf29f575ba730bad898d))
- *(installer)* Missing `gd` extension ([#1749](https://github.com/torrentpier/torrentpier/pull/1749)) - ([a1c519d](https://github.com/torrentpier/torrentpier/commit/a1c519d938b848edffcbf7fbbe6a3fdb9a5394f1))

### 📦 Dependencies

- *(deps)* Bump php-curl-class/php-curl-class from 11.0.0 to 11.0.1 ([#1753](https://github.com/torrentpier/torrentpier/pull/1753)) - ([ce32031](https://github.com/torrentpier/torrentpier/commit/ce32031a0fb14cdf6c3f4ba379b530cbb52b0fea))
- *(deps)* Bump bugsnag/bugsnag from 3.29.1 to 3.29.2 ([#1752](https://github.com/torrentpier/torrentpier/pull/1752)) - ([f63d15c](https://github.com/torrentpier/torrentpier/commit/f63d15c49e3992837413b2c7a0160d599b44f2ef))

### 📚 Documentation

- Minor improvements ([#1750](https://github.com/torrentpier/torrentpier/pull/1750)) - ([3e850ac](https://github.com/torrentpier/torrentpier/commit/3e850ac724c43e813aa077b272b498e2b0477260))

### ⚙️ Miscellaneous

- *(cliff)* Changed emoji for dependencies ([#1755](https://github.com/torrentpier/torrentpier/pull/1755)) - ([55d4670](https://github.com/torrentpier/torrentpier/commit/55d467048370b51cd592982c8026702dca8813d5))
- *(cliff)* Use blockquote for notice ([#1748](https://github.com/torrentpier/torrentpier/pull/1748)) - ([61e5592](https://github.com/torrentpier/torrentpier/commit/61e55925f312417bdb63c88a7c8939c3b2eb2ac5))
- *(cliff)* Fixed typo ([#1747](https://github.com/torrentpier/torrentpier/pull/1747)) - ([4936af7](https://github.com/torrentpier/torrentpier/commit/4936af7d3d10f553d8586a14de249c32e50f3494))
- *(cliff)* Notice about previous changelog file ([#1746](https://github.com/torrentpier/torrentpier/pull/1746)) - ([85395be](https://github.com/torrentpier/torrentpier/commit/85395be5e7c6a891c79ec72cf215894af097f819))
- *(copyright)* Updated copyright year ([#1760](https://github.com/torrentpier/torrentpier/pull/1760)) - ([6697410](https://github.com/torrentpier/torrentpier/commit/6697410c1df6c8d9d7f511b1e984ae90d888ae0e))
- Update `cliff.toml` - ([254dca2](https://github.com/torrentpier/torrentpier/commit/254dca2b27c2d92421d3e639c80b0adf1172202f))
- Minor improvements ([#1743](https://github.com/torrentpier/torrentpier/pull/1743)) - ([e73d650](https://github.com/torrentpier/torrentpier/commit/e73d65011fff0a8b8e1368eef61bbfb67e87eab8))
- Enabled `$bb_cfg['integrity_check']` by defaul ([#1742](https://github.com/torrentpier/torrentpier/pull/1742)) - ([7e3601e](https://github.com/torrentpier/torrentpier/commit/7e3601e63aff73be1428969ca37dda3da2537d9b))
<!-- generated by git-cliff -->
