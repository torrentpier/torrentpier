<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

// Common, these terms are used extensively on several pages
$lang['ADMIN'] = 'Hallinnoinnissa';
$lang['FORUM'] = 'Forum';
$lang['CATEGORY'] = 'Luokka';
$lang['HIDE_CAT'] = 'Piilota luokat';
$lang['HIDE_CAT_MESS'] = 'Jotkut luokat on piilotettu mukautetuilla näyttövaihtoehdoilla';
$lang['SHOW_ALL'] = 'Näytä kaikki';
$lang['TOPIC'] = 'Aihe';
$lang['TOPICS'] = 'Aiheet';
$lang['TOPICS_SHORT'] = 'Aiheet';
$lang['REPLIES'] = 'Vastaukset';
$lang['REPLIES_SHORT'] = 'Vastaukset';
$lang['VIEWS'] = 'Näkymät';
$lang['POSTS'] = 'Viestit';
$lang['POSTS_SHORT'] = 'Viestit';
$lang['POSTED'] = 'Lähetetty';
$lang['USERNAME'] = 'Käyttäjätunnus';
$lang['PASSWORD'] = 'Salasana';
$lang['PASSWORD_SHOW_BTN'] = 'Show password';
$lang['EMAIL'] = 'Sähköposti';
$lang['PM'] = 'PM';
$lang['AUTHOR'] = 'Kirjoittaja';
$lang['TIME'] = 'Aika';
$lang['HOURS'] = 'Tuntia';
$lang['MESSAGE'] = 'Viesti';
$lang['TORRENT'] = 'Torrent';
$lang['PERMISSIONS'] = 'Käyttöoikeudet';
$lang['TYPE'] = 'Tyyppi';
$lang['SEEDER'] = 'Seeder';
$lang['LEECHER'] = 'Leecher';
$lang['RELEASER'] = 'Releaser';

$lang['1_DAY'] = '1 Päivä';
$lang['7_DAYS'] = '7 Päivää';
$lang['2_WEEKS'] = '2 Viikkoa';
$lang['1_MONTH'] = '1 Kuukausi';
$lang['3_MONTHS'] = '3 Kuukautta';
$lang['6_MONTHS'] = '6 Kuukautta';
$lang['1_YEAR'] = '1 Vuosi';

$lang['GO'] = 'Mene';
$lang['SUBMIT'] = 'Lähetä';
$lang['RESET'] = 'Reset';
$lang['CANCEL'] = 'Peruuta';
$lang['PREVIEW'] = 'Esikatselu';
$lang['AJAX_PREVIEW'] = 'Quick View';
$lang['CONFIRM'] = 'Vahvista';
$lang['YES'] = 'Kyllä';
$lang['NO'] = 'Ei';
$lang['ENABLED'] = 'Käytössä';
$lang['DISABLED'] = 'Käytöstä';
$lang['ERROR'] = 'Virhe';
$lang['SELECT_ACTION'] = 'Valitse toiminta';
$lang['CLEAR'] = 'Clear';
$lang['MOVE_TO_TOP'] = 'Move to top';
$lang['UNKNOWN'] = 'Tuntematon';
$lang['COPY_TO_CLIPBOARD'] = 'Copy to clipboard';
$lang['NO_ITEMS'] = 'There seems to be no data here...';
$lang['PLEASE_TRY_AGAIN'] = 'Please try again after few seconds...';

$lang['NEXT_PAGE'] = 'Seuraava';
$lang['PREVIOUS_PAGE'] = 'Edellinen';
$lang['SHORT_PAGE'] = 'sivulle';
$lang['GOTO_PAGE'] = 'Siirry sivulle';
$lang['GOTO_SHORT'] = 'Sivu';
$lang['JOINED'] = 'Liittynyt';
$lang['LONGEVITY'] = 'Rekisteröity';
$lang['IP_ADDRESS'] = 'IP-Osoite';
$lang['POSTED_AFTER'] = 'jälkeen';

$lang['SELECT_FORUM'] = 'Valitse foorumi';
$lang['VIEW_LATEST_POST'] = 'Näytä viimeisin viesti';
$lang['VIEW_NEWEST_POST'] = 'Näytä uusin viesti';
$lang['PAGE_OF'] = 'Sivu <b>%d</b> ja <b>%s</b>';

$lang['ICQ'] = 'ICQ';

$lang['SKYPE'] = 'Skype';
$lang['SKYPE_ERROR'] = 'Olet antanut epäkelvon Skype-kirjautuminen';

$lang['TWITTER'] = 'Twitter';
$lang['TWITTER_ERROR'] = 'Olet antanut epäkelvon Twitter kirjautuminen';

$lang['FORUM_INDEX'] = '%s Forum-Index'; // e.g. sitename Forum Index, %s can be removed if you prefer

$lang['POST_NEW_TOPIC'] = 'Lähetä uusi aihe';
$lang['POST_NEW_RELEASE'] = 'Post uusi julkaisu';
$lang['POST_REGULAR_TOPIC'] = 'Post tavallinen aihe';
$lang['REPLY_TO_TOPIC'] = 'Vastaus aiheesta';
$lang['REPLY_WITH_QUOTE'] = 'Vastaa lainaten';

$lang['CLICK_RETURN_TOPIC'] = 'Klikkaa %sHere%s palata aiheeseen'; // %s's here are for uris, do not remove!
$lang['CLICK_RETURN_LOGIN'] = 'Klikkaa %sHere%s yrittää uudelleen';
$lang['CLICK_RETURN_FORUM'] = 'Klikkaa %sHere%s palata forum';
$lang['CLICK_VIEW_MESSAGE'] = 'Klikkaa %sHere%s palata viesti';
$lang['CLICK_RETURN_MODCP'] = 'Klikkaa %sHere%s palata Moderaattori Ohjauspaneeli';
$lang['CLICK_RETURN_GROUP'] = 'Klikkaa %sHere%s palata ryhmän tiedot';

$lang['ADMIN_PANEL'] = 'Mene hallintapaneeliin';
$lang['ALL_CACHE_CLEARED'] = 'Välimuisti on tyhjennetty';
$lang['ALL_TEMPLATE_CLEARED'] = 'Malli välimuisti on tyhjennetty';
$lang['DATASTORE_CLEARED'] = 'Datastore on selvitetty';
$lang['BOARD_DISABLE'] = 'Anteeksi, tämä foorumi on poistettu käytöstä. Yritä tulla takaisin myöhemmin';
$lang['BOARD_DISABLE_CRON'] = 'Foorumi on suljettu huollon. Yritä tulla takaisin myöhemmin';
$lang['ADMIN_DISABLE'] = 'foorumi on poistettu käytöstä järjestelmänvalvoja, voit ottaa sen käyttöön milloin tahansa';
$lang['ADMIN_DISABLE_CRON'] = 'forum lukittu liipaisimen ajastettu tehtävä, voit poistaa lukituksen tahansa';
$lang['ADMIN_DISABLE_TITLE'] = 'Foorumi on poistettu käytöstä';
$lang['ADMIN_DISABLE_CRON_TITLE'] = 'Foorumi on suljettu huollon';
$lang['ADMIN_UNLOCK'] = 'Jotta forum';
$lang['ADMIN_UNLOCKED'] = 'Auki';
$lang['ADMIN_UNLOCK_CRON'] = 'Poista lukitus';

$lang['LOADING'] = 'Loading...';
$lang['JUMPBOX_TITLE'] = 'Valitse foorumi';
$lang['DISPLAYING_OPTIONS'] = 'Näyttää vaihtoehtoja';

// Global Header strings
$lang['REGISTERED_USERS'] = 'Rekisteröityneet Käyttäjät:';
$lang['BROWSING_FORUM'] = 'Käyttäjiä lukemassa tätä aluetta:';
$lang['ONLINE_USERS'] = 'Paikalla on yhteensä <b>%1$d</b> käyttäjät online: %2$d rekisteröity ja %3$d asiakkaat';
$lang['RECORD_ONLINE_USERS'] = 'Eniten käyttäjiä online on ollut <b>%s</b> on %s'; // first %s = number of users, second %s is the date.

$lang['ONLINE_ADMIN'] = 'Ylläpitäjä';
$lang['ONLINE_MOD'] = 'Moderaattori';
$lang['ONLINE_GROUP_MEMBER'] = 'Ryhmän jäsen';

$lang['CANT_EDIT_IN_DEMO_MODE'] = 'This action can not be performed in demo mode!';

$lang['CURRENT_TIME'] = 'Nykyinen aika on: <span class="tz_time">%s</span>';

$lang['SEARCH_NEW'] = 'Näytä uusimmat viestit';
$lang['SEARCH_SELF'] = 'Minun virkaa';
$lang['SEARCH_SELF_BY_LAST'] = 'viimeinen viesti aika';
$lang['SEARCH_SELF_BY_MY'] = 'minun post aikaa';
$lang['SEARCH_UNANSWERED'] = 'Näytä vastaamattomat viestit';
$lang['SEARCH_UNANSWERED_SHORT'] = 'vastattu';
$lang['SEARCH_LATEST'] = 'Uusimmat aiheet';
$lang['LATEST_RELEASES'] = 'Uusimmat tiedotteet';

$lang['REGISTER'] = 'Rekisteriin';
$lang['PROFILE'] = 'Profiili';
$lang['EDIT_PROFILE'] = 'Muokkaa profiilia';
$lang['SEARCH'] = 'Haku';
$lang['MEMBERLIST'] = 'Käyttäjälistaa';
$lang['USERGROUPS'] = 'Käyttäjäryhmät';
$lang['LASTPOST'] = 'Viimeisin Viesti';
$lang['MODERATOR'] = 'Moderaattori';
$lang['MODERATORS'] = 'Moderaattorit';
$lang['TERMS'] = 'Ehdot';
$lang['NOTHING_HAS_CHANGED'] = 'Mitään ei ole muuttunut';

// Stats block text
$lang['POSTED_TOPICS_TOTAL'] = 'Käyttäjämme ovat kirjoittaneet yhteensä <b>%s</b> aiheita'; // Number of topics
$lang['POSTED_ARTICLES_ZERO_TOTAL'] = 'Käyttäjämme ovat kirjoittaneet yhteensä <b>0</b> artikkeleita'; // Number of posts
$lang['POSTED_ARTICLES_TOTAL'] = 'Käyttäjämme ovat kirjoittaneet yhteensä <b>%s</b> artikkeleita'; // Number of posts
$lang['REGISTERED_USERS_ZERO_TOTAL'] = 'Meillä on <b>0</b> rekisteröityneet käyttäjät'; // # registered users
$lang['REGISTERED_USERS_TOTAL'] = 'Meillä on <b>%s</b> rekisteröityneet käyttäjät'; // # registered users
$lang['USERS_TOTAL_GENDER'] = 'Pojat: <b>%d</b>, Tytöt: <b>%d</b>, Muut: <b>%d</b>';
$lang['NEWEST_USER'] = 'Uusin rekisteröitynyt käyttäjä on <b>%s</b>'; // a href, username, /a

// Tracker stats
$lang['TORRENTS_STAT'] = 'Torrentit: <b style="color: blue;">%s</b>,&nbsp; Yhteensä koko: <b>%s</b>'; // first %s = number of torrents, second %s is the total size.
$lang['PEERS_STAT'] = 'Ikäisensä: <b>%s</b>,&nbsp; Kylvökoneet: <b class="seedmed">%s</b>,&nbsp; Leechers: <b class="leechmed">%s</b>'; // first %s = number of peers, second %s = number of seeders,  third %s = number of leechers.
$lang['SPEED_STAT'] = 'Yhteensä nopeus: <b>%s</b>&nbsp;'; // %s = total speed.

$lang['NO_NEW_POSTS_LAST_VISIT'] = 'Ei uusia viestejä viime käynnin jälkeen';
$lang['NO_NEW_POSTS'] = 'Ei uusia viestejä';
$lang['NEW_POSTS'] = 'Uusia viestejä';
$lang['NEW_POST'] = 'Uusi viesti';
$lang['NO_NEW_POSTS_HOT'] = 'Ei uusia viestejä [ Suosittu ]';
$lang['NEW_POSTS_HOT'] = 'Uusia viestejä [ Suosittu ]';
$lang['NEW_POSTS_LOCKED'] = 'Uusia viestejä [ Lukittu ]';
$lang['FORUM_LOCKED_MAIN'] = 'Foorumi on lukittu';

// Login
$lang['ENTER_PASSWORD'] = 'Ole hyvä ja anna käyttäjätunnus ja salasana kirjautua sisään.';
$lang['LOGIN'] = 'Kirjaudu sisään';
$lang['LOGOUT'] = 'Kirjaudu ulos';
$lang['CONFIRM_LOGOUT'] = 'Oletko varma, että haluat kirjautua ulos?';

$lang['FORGOTTEN_PASSWORD'] = 'Salasana unohtunut?';
$lang['AUTO_LOGIN'] = 'Kirjaa minut automaattisesti';
$lang['ERROR_LOGIN'] = 'Käyttäjätunnus sinulle on toimitettu väärä tai virheellinen, tai salasana on virheellinen.';
$lang['REMEMBER'] = 'Muista';
$lang['USER_WELCOME'] = 'Tervetuloa,';

// Index page
$lang['HOME'] = 'Kotiin';
$lang['NO_POSTS'] = 'Ei virkaa';
$lang['NO_FORUMS'] = 'Tämä hallitus ei ole foorumeilla';

$lang['PRIVATE_MESSAGE'] = 'Yksityinen Viesti';
$lang['PRIVATE_MESSAGES'] = 'Yksityiset Viestit';
$lang['WHOSONLINE'] = 'Kuka on verkossa';

$lang['MARK_ALL_FORUMS_READ'] = 'Lippu kaikki foorumit luetuiksi';
$lang['FORUMS_MARKED_READ'] = 'Kaikki foorumit merkitty luetuksi';

$lang['LATEST_NEWS'] = 'Uusimmat uutiset';
$lang['NETWORK_NEWS'] = 'Verkon uutisia';
$lang['SUBFORUMS'] = 'Subforums';

// Viewforum
$lang['VIEW_FORUM'] = 'View Forum';

$lang['FORUM_NOT_EXIST'] = 'Foorumi olet valinnut ei ole olemassa.';
$lang['REACHED_ON_ERROR'] = 'Olet saavuttanut tämän sivun virhe.';
$lang['ERROR_PORNO_FORUM'] = 'Tämän tyyppinen foorumeilla (18+) oli piilotettu oman profiili, sinun';

$lang['DISPLAY_TOPICS'] = 'Näyttää aiheet';
$lang['ALL_TOPICS'] = 'Kaikki Aiheet';
$lang['MODERATE_FORUM'] = 'Kohtalainen tällä foorumilla';
$lang['TITLE_SEARCH_HINT'] = 'etsi otsikko...';

$lang['TOPIC_ANNOUNCEMENT'] = 'Ilmoitus:';
$lang['TOPIC_STICKY'] = 'Sticky:';
$lang['TOPIC_MOVED'] = 'Muutti:';
$lang['TOPIC_POLL'] = '[ Poll ]';

$lang['MARK_TOPICS_READ'] = 'Merkitse kaikki aiheet luetuiksi';
$lang['TOPICS_MARKED_READ'] = 'Aiheita tällä foorumilla on vain ollut merkitty lukea';

$lang['RULES_POST_CAN'] = 'Sinun <b>can</b> kirjoittaa uusia viestejä tässä foorumissa';
$lang['RULES_POST_CANNOT'] = 'Sinun <b>cannot</b> kirjoittaa uusia viestejä tässä foorumissa';
$lang['RULES_REPLY_CAN'] = 'Sinun <b>can</b> vastata viesteihin tässä foorumissa';
$lang['RULES_REPLY_CANNOT'] = 'Sinun <b>cannot</b> vastata viesteihin tässä foorumissa';
$lang['RULES_EDIT_CAN'] = 'Sinun <b>can</b> muokata viestejäsi tässä foorumissa';
$lang['RULES_EDIT_CANNOT'] = 'Sinun <b>cannot</b> muokata viestejäsi tässä foorumissa';
$lang['RULES_DELETE_CAN'] = 'Sinun <b>can</b> poistaa viestejäsi tässä foorumissa';
$lang['RULES_DELETE_CANNOT'] = 'Sinun <b>cannot</b> poistaa viestejäsi tässä foorumissa';
$lang['RULES_VOTE_CAN'] = 'Sinun <b>can</b> äänestää tässä foorumissa';
$lang['RULES_VOTE_CANNOT'] = 'Sinun <b>cannot</b> äänestää tässä foorumissa';
$lang['RULES_MODERATE'] = 'Sinun <b>can</b> kohtalainen tällä foorumilla';

$lang['NO_TOPICS_POST_ONE'] = 'There are no posts in this forum yet<br />Click on the <b>New Topic</b> icon, and your post will be the first.';
$lang['NO_RELEASES_POST_ONE'] = 'There are no releases in this forum yet<br />Click on the <b>New Release</b> icon, and your release will be the first.';

// Viewtopic
$lang['VIEW_TOPIC'] = 'View topic';

$lang['GUEST'] = 'Vieras';
$lang['POST_SUBJECT'] = 'Viesti aihe';
$lang['SUBMIT_VOTE'] = 'Toimitettava äänestys';
$lang['VIEW_RESULTS'] = 'Näytä tulokset';

$lang['NO_NEWER_TOPICS'] = 'Ei ole uudempia aiheita tällä foorumilla';
$lang['NO_OLDER_TOPICS'] = 'Ei ole vanhempia aiheita tällä foorumilla';
$lang['TOPIC_POST_NOT_EXIST'] = 'Aihe tai lähettää sinulle pyytää ei ole olemassa';
$lang['NO_POSTS_TOPIC'] = 'Ei ole viestiä tässä ketjussa';

$lang['DISPLAY_POSTS'] = 'Näytä viestit';
$lang['ALL_POSTS'] = 'Kaikki Viestit';
$lang['NEWEST_FIRST'] = 'Uusin Ensin';
$lang['OLDEST_FIRST'] = 'Vanhin Ensin';

$lang['BACK_TO_TOP'] = 'Takaisin alkuun';

$lang['READ_PROFILE'] = 'Näytä käyttäjän profiili';
$lang['VISIT_WEBSITE'] = 'Käy juliste: n verkkosivuilla';
$lang['VIEW_IP'] = 'Näytä juliste IP-osoite';
$lang['MODERATE_POST'] = 'Kohtalainen viestit';
$lang['DELETE_POST'] = 'Poista tämä viesti';

$lang['WROTE'] = 'kirjoitti'; // proceeds the username and is followed by the quoted text
$lang['QUOTE'] = 'Lainaus'; // comes before bbcode quote output
$lang['CODE'] = 'Koodi'; // comes before bbcode code output
$lang['SPOILER_HEAD'] = 'piilotettu teksti';
$lang['SPOILER_CLOSE'] = 'käännä';
$lang['PLAY_ON_CURPAGE'] = 'Aloittaa pelaamisen nykyisen sivun';

$lang['EDITED_TIME_TOTAL'] = 'Last edited by <b>%s</b> on %s; edited %d time in total'; // Last edited by me on 12 Oct 2001; edited 1 time in total
$lang['EDITED_TIMES_TOTAL'] = 'Last edited by <b>%s</b> on %s; edited %d times in total'; // Last edited by me on 12 Oct 2001; edited 2 times in total

$lang['LOCK_TOPIC'] = 'Lukitse aihe';
$lang['UNLOCK_TOPIC'] = 'Avaa aihe';
$lang['MOVE_TOPIC'] = 'Siirrä aihe';
$lang['DELETE_TOPIC'] = 'Poista aihe';
$lang['SPLIT_TOPIC'] = 'Split aihe';

$lang['STOP_WATCHING_TOPIC'] = 'Lopeta seuraava aihe';
$lang['START_WATCHING_TOPIC'] = 'Seuraa aiheen vastauksia';
$lang['NO_LONGER_WATCHING'] = 'Et enää seuraa tätä aihetta';
$lang['YOU_ARE_WATCHING'] = 'Olet tämän aiheen nyt';

$lang['TOTAL_VOTES'] = 'Yhteensä Ääniä';
$lang['SEARCH_IN_TOPIC'] = 'etsi aihe...';
$lang['HIDE_IN_TOPIC'] = 'Piilota';

$lang['SHOW'] = 'Esityksessä';
$lang['AVATARS'] = 'Avatarit';
$lang['RANK_IMAGES'] = 'Sijoitus kuvia';
$lang['POST_IMAGES'] = 'Post kuvia';
$lang['SIGNATURES'] = 'Allekirjoitukset';
$lang['SPOILER'] = 'Spoileri';
$lang['SHOW_OPENED'] = 'Näyttely avattiin';
$lang['DOWNLOAD_PIC'] = 'Ladattavat kuvat';

$lang['MODERATE_TOPIC'] = 'Kohtalainen tästä aiheesta';
$lang['SELECT_POSTS_PER_PAGE'] = 'viestejä per sivu';

// Posting/Replying (Not private messaging!)
$lang['TOPIC_REVIEW'] = 'Aihe arvostelu';

$lang['NO_POST_MODE'] = 'Ei post-tila valittuna'; // If posting.php is called without a mode (newtopic/reply/delete/etc., shouldn't be shown normally)

$lang['POST_A_NEW_TOPIC'] = 'Lähetä uusi aihe';
$lang['POST_A_REPLY'] = 'Post new reply';
$lang['POST_TOPIC_AS'] = 'Post aihe kuin';
$lang['EDIT_POST'] = 'Edit post';
$lang['EDIT_TOPIC_TITLE'] = 'Muokata ketjun otsikkoa';
$lang['EDIT_POST_NOT_1'] = 'Et saa ';
$lang['EDIT_POST_NOT_2'] = 'Et voi ';
$lang['EDIT_POST_AJAX'] = 'Et voi muokata post tila ';
$lang['AFTER_THE_LAPSE'] = 'jälkeen kulunut ';

$lang['DONT_MESSAGE_TITLE'] = 'Sinun pitäisi määrittää viestin otsikko';
$lang['INVALID_TOPIC_ID'] = 'Aihe Puuttuu!';
$lang['INVALID_TOPIC_ID_DB'] = 'Aiheesta ei ole olemassa tietokannassa!';

$lang['NOT_POST'] = 'Poissa Viesti';
$lang['NOT_EDIT_TOR_STATUS'] = 'Et voi muokata julkaisun tila';
$lang['TOR_STATUS_DAYS'] = 'päivää';

$lang['OPTIONS'] = 'Vaihtoehtoja';

$lang['POST_ANNOUNCEMENT'] = 'Ilmoitus';
$lang['POST_STICKY'] = 'Tahmea';
$lang['POST_NORMAL'] = 'Normaali';
$lang['POST_DOWNLOAD'] = 'Lataa';

$lang['PRINT_PAGE'] = 'Print page';

$lang['CONFIRM_DELETE'] = 'Oletko varma, että haluat poistaa tämän postin?';
$lang['CONFIRM_DELETE_POLL'] = 'Oletko varma, että haluat poistaa tämän kyselyn?';

$lang['FLOOD_ERROR'] = 'Et voi tehdä toiseen virkaan, niin pian sen jälkeen, kun viimeinen, ole hyvä ja yritä uudelleen hetken kuluttua';
$lang['EMPTY_SUBJECT'] = 'Sinun on määritettävä aihe';
$lang['EMPTY_MESSAGE'] = 'Sinun täytyy syöttää viestin';
$lang['FORUM_LOCKED'] = 'Tämä foorumi on lukittu: ei voi lähettää, vastata tai muokata aiheita';
$lang['TOPIC_LOCKED'] = 'Tämä aihe on lukittu, et voi muokata vastauksia tai tehdä vastaukset';
$lang['TOPIC_LOCKED_SHORT'] = 'Aihe lukittu';
$lang['NO_POST_ID'] = 'Sinun täytyy valita viesti muokkaa';
$lang['NO_TOPIC_ID'] = 'Sinun täytyy valita aihe, vastata';
$lang['NO_VALID_MODE'] = 'Voit vain postitse, vastata, muokata tai lainata viestejä. Palaa ja yritä uudelleen';
$lang['NO_SUCH_POST'] = 'Ei ole olemassa sellaista postitse. Palaa ja yritä uudelleen';
$lang['EDIT_OWN_POSTS'] = 'Anteeksi, mutta voit muokata vain omia viestejäsi';
$lang['DELETE_OWN_POSTS'] = 'Anteeksi, mutta voit poistaa vain omia viestejäsi';
$lang['CANNOT_DELETE_REPLIED'] = 'Pahoillani, mutta et voi poistaa viestejä, jotka ovat olleet vastasi';
$lang['CANNOT_DELETE_POLL'] = 'Pahoillani, mutta et voi poistaa aktiivista kysely';
$lang['EMPTY_POLL_TITLE'] = 'Sinun täytyy syöttää nimi kyselyn';
$lang['TO_FEW_POLL_OPTIONS'] = 'Sinun täytyy syöttää vähintään kaksi kyselyn vaihtoehtoja';
$lang['TO_MANY_POLL_OPTIONS'] = 'Olet yrittänyt syöttää liian monet kyselyn vaihtoehtoja';
$lang['POST_HAS_NO_POLL'] = 'Tämä viesti on ei kyselyn';
$lang['ALREADY_VOTED'] = 'Sinulla on jo äänestänyt tässä kyselyssä';
$lang['NO_VOTE_OPTION'] = 'Sinun on määritettävä vaihtoehto, kun äänestää';
$lang['LOCKED_WARN'] = 'Lähetetty osaksi lukittu aihe!';

$lang['ADD_POLL'] = 'Lisää kyselyn';
$lang['ADD_POLL_EXPLAIN'] = 'Jos et halua lisätä kyselyn aihe, jätä kentät tyhjiksi.';
$lang['POLL_QUESTION'] = 'Kyselyn kysymys';
$lang['POLL_OPTION'] = 'Kyselyn vaihtoehto';
$lang['ADD_OPTION'] = 'Lisää vaihtoehto';
$lang['UPDATE'] = 'Päivitys';
$lang['POLL_FOR'] = 'Suorittaa kyselyn';
$lang['DAYS'] = 'Päivää';
$lang['POLL_FOR_EXPLAIN'] = '[ Enter 0 tai jätä tyhjäksi loputon kysely ]';
$lang['DELETE_POLL'] = 'Poista kysely';

$lang['MAX_SMILIES_PER_POST'] = 'Hymiöitä raja %s hymiöitä ylitetty.';

$lang['ATTACH_SIGNATURE'] = 'Liitä allekirjoitus (allekirjoitusta voidaan vaihtaa profiilin)';
$lang['NOTIFY'] = 'Ilmoita minulle, kun vastaukset';
$lang['ALLOW_ROBOTS_INDEXING'] = 'Allow robots indexing this topic';

$lang['STORED'] = 'Viestisi on kirjattu onnistuneesti.';
$lang['EDITED'] = 'Viesti on muutettu';
$lang['DELETED'] = 'Viestisi on poistettu onnistuneesti.';
$lang['POLL_DELETE'] = 'Kysely on poistettu onnistuneesti.';
$lang['VOTE_CAST'] = 'Äänestys on valettu.';

$lang['EMOTICONS'] = 'Hymiöitä';
$lang['MORE_EMOTICONS'] = 'Katso lisää Hymiöitä';

$lang['FONT_COLOR'] = 'Fontin väri';
$lang['COLOR_DEFAULT'] = 'Oletuksena';
$lang['COLOR_DARK_RED'] = 'Tumma Punainen';
$lang['COLOR_RED'] = 'Punainen';
$lang['COLOR_ORANGE'] = 'Oranssi';
$lang['COLOR_BROWN'] = 'Ruskea';
$lang['COLOR_YELLOW'] = 'Keltainen';
$lang['COLOR_GREEN'] = 'Vihreä';
$lang['COLOR_OLIVE'] = 'Oliivi';
$lang['COLOR_CYAN'] = 'Syaani';
$lang['COLOR_BLUE'] = 'Sininen';
$lang['COLOR_DARK_BLUE'] = 'Tumma Sininen';
$lang['COLOR_INDIGO'] = 'Indigo';
$lang['COLOR_VIOLET'] = 'Violetti';
$lang['COLOR_WHITE'] = 'Valkoinen';
$lang['COLOR_BLACK'] = 'Musta';

$lang['FONT_SIZE'] = 'Fontin kokoa';
$lang['FONT_TINY'] = 'Pieni';
$lang['FONT_SMALL'] = 'Pieni';
$lang['FONT_NORMAL'] = 'Normaali';
$lang['FONT_LARGE'] = 'Suuri';
$lang['FONT_HUGE'] = 'Valtava';

$lang['STYLES_TIP'] = 'Vihje: Tyylejä voidaan soveltaa nopeasti valitun tekstin.';

$lang['NEW_POSTS_PREVIEW'] = 'Aihe on uusi, muokata tai lukemattomat viestit';

// Private Messaging
$lang['PRIVATE_MESSAGING'] = 'Yksityisviestit';

$lang['NO_NEW_PM'] = 'ei uusia viestejä';

$lang['NEW_PMS_FORMAT'] = '<b>%1$s</b> %2$s'; // 1 new message
$lang['NEW_PMS_DECLENSION'] = ['uusi viesti', 'uusia viestejä'];

$lang['UNREAD_PMS_FORMAT'] = '<b>%1$s</b> %2$s'; // 1 new message
$lang['UNREAD_PMS_DECLENSION'] = ['lukematon', 'lukematon'];

$lang['UNREAD_MESSAGE'] = 'Lukematon viesti';
$lang['READ_MESSAGE'] = 'Lue viesti';

$lang['READ_PM'] = 'Lue viesti';
$lang['POST_NEW_PM'] = 'Viesti viestin';
$lang['POST_REPLY_PM'] = 'Vastaa viestiin';
$lang['POST_QUOTE_PM'] = 'Lainaus viesti';
$lang['EDIT_PM'] = 'Muokkaa viesti';

$lang['INBOX'] = 'Saapuneet';
$lang['OUTBOX'] = 'Lähtevät';
$lang['SAVEBOX'] = 'Savebox';
$lang['SENTBOX'] = 'Sentbox';
$lang['FLAG'] = 'Lippu';
$lang['SUBJECT'] = 'Aihe';
$lang['FROM'] = 'Alkaen';
$lang['TO'] = 'Voit';
$lang['DATE'] = 'Päivämäärä';
$lang['MARK'] = 'Mark';
$lang['SENT'] = 'Lähettänyt';
$lang['SAVED'] = 'Tallennettu';
$lang['DELETE_MARKED'] = 'Poista Valitut';
$lang['DELETE_ALL'] = 'Poista Kaikki';
$lang['SAVE_MARKED'] = 'Tallenna Merkitty';
$lang['SAVE_MESSAGE'] = 'Tallenna Viesti';
$lang['DELETE_MESSAGE'] = 'Poista Viesti';

$lang['DISPLAY_MESSAGES'] = 'Näytön viestit'; // Followed by number of days/weeks/months
$lang['ALL_MESSAGES'] = 'Kaikki Viestit';

$lang['NO_MESSAGES_FOLDER'] = 'Ei ole viestejä tässä kansiossa';

$lang['PM_DISABLED'] = 'Yksityisviestit on poistettu käytöstä tämän hallituksen.';
$lang['CANNOT_SEND_PRIVMSG'] = 'Anteeksi, mutta ylläpito on estänyt sinua lähettämästä viestejä.';
$lang['NO_TO_USER'] = 'Sinun täytyy määrittää käyttäjätunnus, jolle haluat lähettää viestin.';
$lang['NO_SUCH_USER'] = 'Pahoillani, mutta ei niin käyttäjä on olemassa.';

$lang['DISABLE_BBCODE_PM'] = 'Disable BBCode tämän viestin';
$lang['DISABLE_SMILIES_PM'] = 'Poista Hymiöt tämän viestin';

$lang['MESSAGE_SENT'] = '<b>Your viesti on lähetetty.</b>';

$lang['CLICK_RETURN_INBOX'] = 'Paluu:<br /><br /> %s<b>Inbox</b>%s';
$lang['CLICK_RETURN_SENTBOX'] = '&nbsp;&nbsp; %s<b>Sentbox</b>%s';
$lang['CLICK_RETURN_OUTBOX'] = '&nbsp;&nbsp; %s<b>Outbox</b>%s';
$lang['CLICK_RETURN_SAVEBOX'] = '&nbsp;&nbsp; %s<b>Savebox</b>%s';
$lang['CLICK_RETURN_INDEX'] = '%sReturn, että Index%s';

$lang['SEND_A_NEW_MESSAGE'] = 'Lähetä uusi yksityinen viesti';
$lang['SEND_A_REPLY'] = 'Vastaus yksityiseen viestiin';
$lang['EDIT_MESSAGE'] = 'Edit yksityinen viesti';

$lang['NOTIFICATION_SUBJECT'] = 'New Private Message has been received!';

$lang['FIND_USERNAME'] = 'Etsi käyttäjätunnus';
$lang['SELECT_USERNAME'] = 'Valitse Käyttäjätunnus';
$lang['FIND'] = 'Löytää';
$lang['NO_MATCH'] = 'Ei osumia.';

$lang['NO_PM_ID'] = 'Määritä post ID';
$lang['NO_SUCH_FOLDER'] = 'Kansiota ei löydy';
$lang['NO_FOLDER'] = 'Määritä kansio';

$lang['MARK_ALL'] = 'Merkitse kaikki';
$lang['UNMARK_ALL'] = 'Poista kaikki';

$lang['CONFIRM_DELETE_PM'] = 'Oletko varma, että haluat poistaa tämän viestin?';
$lang['CONFIRM_DELETE_PMS'] = 'Oletko varma, että haluat poistaa nämä viestit?';

$lang['INBOX_SIZE'] = 'Saapuneet is<br /><b>%d%%</b> täynnä'; // e.g. Your Inbox is 50% full
$lang['SENTBOX_SIZE'] = 'Oman Lähetetyt is<br /><b>%d%%</b> täynnä';
$lang['SAVEBOX_SIZE'] = 'Sinun Savebox is<br /><b>%d%%</b> täynnä';

$lang['CLICK_VIEW_PRIVMSG'] = 'Klikkaa %sHere%s vierailla sähköpostiisi';

$lang['OUTBOX_EXPL'] = '';

// Profiles/Registration
$lang['VIEWING_USER_PROFILE'] = 'Katselu profiili :: %s';
$lang['VIEWING_MY_PROFILE'] = 'Oma profiili [ <a href="%s">Settings / Muutos profile</a> ]';

$lang['DISABLED_USER'] = 'Tilin käytöstä';
$lang['MANAGE_USER'] = 'Hallinto';

$lang['PREFERENCES'] = 'Mieltymykset';
$lang['ITEMS_REQUIRED'] = 'Kohteet on merkitty * ovat pakollisia ellei toisin mainita.';
$lang['REGISTRATION_INFO'] = 'Rekisteröintitiedot';
$lang['PROFILE_INFO'] = 'Profiilin Tiedot';
$lang['PROFILE_INFO_WARN'] = 'Julkisesti saatavilla olevat tiedot';
$lang['AVATAR_PANEL'] = 'Avatar ohjauspaneeli';

$lang['WEBSITE'] = 'Sivustolla';
$lang['LOCATION'] = 'Sijainti';
$lang['CONTACT'] = 'Ota yhteyttä';
$lang['EMAIL_ADDRESS'] = 'E-mail-osoite';
$lang['SEND_PRIVATE_MESSAGE'] = 'Lähetä yksityinen viesti';
$lang['HIDDEN_EMAIL'] = '[ Piilotettu ]';
$lang['INTERESTS'] = 'Etuja';
$lang['OCCUPATION'] = 'Ammatti';
$lang['POSTER_RANK'] = 'Juliste listalla';
$lang['AWARDED_RANK'] = 'Myönnetty listalla';
$lang['SHOT_RANK'] = 'Laukaus sijoitus';

$lang['TOTAL_POSTS'] = 'Viestejä yhteensä';
$lang['SEARCH_USER_POSTS'] = 'Löytää virkaa'; // Find all posts by username
$lang['SEARCH_USER_POSTS_SHORT'] = 'Etsi käyttäjän viestit';
$lang['SEARCH_USER_TOPICS'] = 'Etsi käyttäjä aiheita'; // Find all topics by username

$lang['NO_USER_ID_SPECIFIED'] = 'Anteeksi, mutta että käyttäjää ei ole olemassa.';
$lang['WRONG_PROFILE'] = 'Et voi muokata profiilia, joka ei ole oma.';

$lang['ONLY_ONE_AVATAR'] = 'Only one type of avatar can be specified';
$lang['FILE_NO_DATA'] = 'Tiedoston URL-osoite, jonka annoit ei sisällä tietoja';
$lang['NO_CONNECTION_URL'] = 'Yhteys ei kuitenkaan ole voitu URL annoit';
$lang['INCOMPLETE_URL'] = 'Antamasi verkko-osoite on epätäydellinen';
$lang['NO_SEND_ACCOUNT_INACTIVE'] = 'Anteeksi, mutta salasana ei voi palauttaa, koska tilisi on tällä hetkellä aktiivinen';
$lang['NO_SEND_ACCOUNT'] = 'Anteeksi, mutta salasanan voi palauttaa. Ota yhteyttä foorumin ylläpitäjään';

$lang['ALWAYS_ADD_SIG'] = 'Kiinnitä aina minun allekirjoitus';
$lang['HIDE_PORN_FORUMS'] = 'Piilottaa sisällön 18+';
$lang['ADD_RETRACKER'] = 'Lisää retracker torrent-tiedostoja';
$lang['ALWAYS_NOTIFY'] = 'Aina ilmoita vastauksista';
$lang['ALWAYS_NOTIFY_EXPLAIN'] = 'Lähettää sähköpostia kun joku vastaa aihe olet lähetetty. Tämä voidaan muuttaa aina, kun lähetät.';

$lang['BOARD_LANG'] = 'Hallitus kieli';
$lang['GENDER'] = 'Sukupuolten';
$lang['GENDER_SELECT'] = [
    0 => 'Tuntematon',
    1 => 'Mies',
    2 => 'Nainen'
];
$lang['MODULE_OFF'] = 'Moduuli on poistettu käytöstä!';

$lang['BIRTHDAY'] = 'Syntymäpäivä';
$lang['HAPPY_BIRTHDAY'] = 'Hyvää Syntymäpäivää!';
$lang['WRONG_BIRTHDAY_FORMAT'] = 'Syntymäpäivä muodossa oli kirjoitettu väärin.';
$lang['AGE'] = 'Ikä';
$lang['BIRTHDAY_TO_HIGH'] = 'Anteeksi, tämä sivusto ei hyväksy käyttäjä vanhempi kuin %d vuotta vanha';
$lang['BIRTHDAY_TO_LOW'] = 'Anteeksi, tämä sivusto ei hyväksy käyttäjä yonger kuin %d vuotta vanha';
$lang['BIRTHDAY_TODAY'] = 'Käyttäjät, joilla on syntymäpäivä tänään: ';
$lang['BIRTHDAY_WEEK'] = 'Käyttäjät, joilla on syntymäpäivä seuraavan %d päivää: %s';
$lang['NOBIRTHDAY_WEEK'] = 'Ei-käyttäjät, joilla on syntymäpäivä tulevan %d päivää'; // %d is substitude with the number of days
$lang['NOBIRTHDAY_TODAY'] = 'Kenelläkään käyttäjistä ei ole syntymäpäivää tänään';
$lang['BIRTHDAY_ENABLE'] = 'Jotta syntymäpäivä';
$lang['BIRTHDAY_MAX_AGE'] = 'Max ikä';
$lang['BIRTHDAY_MIN_AGE'] = 'Min ikä';
$lang['BIRTHDAY_CHECK_DAY'] = 'Päivää tarkistaa tule pian syntymäpäivät';
$lang['YEARS'] = 'Vuotta';

$lang['NO_THEMES'] = 'Teemoja ei tietokannassa';
$lang['TIMEZONE'] = 'Aikavyöhyke';
$lang['DATE_FORMAT_PROFILE'] = 'Päivämäärän muoto';
$lang['DATE_FORMAT_EXPLAIN'] = 'Syntaksi käyttää on identtinen PHP <a href=\'https://www.php.net/manual/en/function.date.php\' target=\'_other\'>date()</a> toiminto.';
$lang['SIGNATURE'] = 'Allekirjoitus';
$lang['SIGNATURE_EXPLAIN'] = 'Tämä on lohko tekstiä, joka voidaan lisätä virkoja. On %d merkin raja';
$lang['SIGNATURE_DISABLE'] = 'Allekirjoitettu pois rikkoo foorumin sääntöjä';
$lang['PUBLIC_VIEW_EMAIL'] = 'Näytä e-mail address in my profile';

$lang['EMAIL_EXPLAIN'] = 'Tähän osoitteeseen sinulle lähetetään rekisteröinnin loppuun';

$lang['CURRENT_PASSWORD'] = 'Nykyinen salasana';
$lang['NEW_PASSWORD'] = 'Uusi salasana';
$lang['CONFIRM_PASSWORD'] = 'Vahvista salasana';
$lang['CONFIRM_PASSWORD_EXPLAIN'] = 'Sinun täytyy vahvista nykyinen salasana, jos haluat muuttaa sitä tai muuttaa your e-mail osoite';
$lang['PASSWORD_IF_CHANGED'] = 'Sinun tarvitsee vain antaa salasana, jos haluat muuttaa sitä';
$lang['PASSWORD_CONFIRM_IF_CHANGED'] = 'Sinun tarvitsee vain vahvista salasana, jos olet vaihtanut sitä ennen';

$lang['AUTOLOGIN'] = 'Autologin';
$lang['RESET_AUTOLOGIN'] = 'Palauta avain autologin';
$lang['RESET_AUTOLOGIN_EXPL'] = 'kuten kaikki paikkoja, joissa olet käynyt foorumilla käytössä automaattinen kirjautuminen';

$lang['AVATAR'] = 'Avatar';
$lang['AVATAR_EXPLAIN'] = 'Displays a small graphic image below your details in posts. Only one image can be displayed at a time, its width can be no greater than %d pixels, the height no greater than %d pixels, and the file size no more than %s.';
$lang['AVATAR_DELETE'] = 'Poista avatar';
$lang['AVATAR_DISABLE'] = 'Avatar-ohjaus-vaihtoehto on pois käytöstä vastoin <a href="%s"><b>forum rules</b></a>';
$lang['UPLOAD_AVATAR_FILE'] = 'Ladata avatar';

$lang['SELECT_AVATAR'] = 'Valitse avatar';
$lang['RETURN_PROFILE'] = 'Paluu profiili';
$lang['SELECT_CATEGORY'] = 'Valitse luokka';

$lang['DELETE_IMAGE'] = 'Poista kuva';
$lang['SET_MONSTERID_AVATAR'] = 'Set MonsterID avatar';
$lang['CURRENT_IMAGE'] = 'Nykyisen kuvan';

$lang['NOTIFY_ON_PRIVMSG'] = 'Ilmoita uusi yksityinen viesti';
$lang['HIDE_USER'] = 'Piilota online status';
$lang['HIDDEN_USER'] = 'Piilotettu käyttäjä';

$lang['PROFILE_UPDATED'] = 'Profiilisi on päivitetty';
$lang['PROFILE_UPDATED_INACTIVE'] = 'Profiilisi on päivitetty. Kuitenkin, sinun on muuttunut elintärkeitä yksityiskohtia, jolloin tilisi on nyt aktiivinen. Tarkista e-mail selvittää, miten aktivoida tilisi, tai jos admin aktivointi on tarpeen, odota, että ylläpitäjä aktivoi se.';

$lang['PASSWORD_MISMATCH'] = 'Salasanat eivät täsmää.';
$lang['CURRENT_PASSWORD_MISMATCH'] = 'Nykyinen salasanasi, jotka annoit ei ole ottelu, joka tallennetaan tietokantaan.';
$lang['PASSWORD_LONG'] = 'Your password must be no longer than %d characters and no shorter than %d characters.';
$lang['TOO_MANY_REGISTERS'] = 'Olet tehnyt liian monta rekisteröinti yritykset. Yritä myöhemmin uudelleen.';
$lang['USERNAME_TAKEN'] = 'Anteeksi, mutta tämä käyttäjätunnus on jo toteutettu.';
$lang['USERNAME_INVALID'] = 'Anteeksi, mutta tämä käyttäjätunnus on virheellinen merkki';
$lang['USERNAME_DISALLOWED'] = 'Anteeksi, mutta tämä käyttäjätunnus on hyväksytty.';
$lang['USERNAME_TOO_LONG'] = 'Nimesi on liian pitkä.';
$lang['USERNAME_TOO_SMALL'] = 'Nimesi on liian pieni.';
$lang['EMAIL_TAKEN'] = 'Anteeksi, mutta että e-mail-osoite on jo rekisteröity käyttäjä.';
$lang['EMAIL_BANNED'] = 'Anteeksi, mutta <b>%s</b>-osoite on kielletty.';
$lang['EMAIL_INVALID'] = 'Anteeksi, mutta tämä e-mail-osoite on virheellinen.';
$lang['EMAIL_TOO_LONG'] = 'Sähköpostiosoite on liian pitkä.';
$lang['SIGNATURE_TOO_LONG'] = 'Allekirjoituksesi on liian pitkä.';
$lang['SIGNATURE_ERROR_HTML'] = 'Allekirjoitus voi sisältää ainoastaan BBCode';
$lang['FIELDS_EMPTY'] = 'Sinun täytyy täyttää vaaditut kentät.';

$lang['WELCOME_SUBJECT'] = 'Tervetuloa %s Foorumeilla'; // Welcome to my.com forums
$lang['NEW_ACCOUNT_SUBJECT'] = 'Uusi käyttäjätili';
$lang['ACCOUNT_ACTIVATED_SUBJECT'] = 'Tili Aktivoitu';

$lang['ACCOUNT_ADDED'] = 'Kiitos rekisteröitymisestä. Tilisi on luotu. Voit nyt kirjautua sisään käyttäjätunnuksella ja salasanalla';
$lang['ACCOUNT_INACTIVE'] = 'Tilisi on luotu. Kuitenkin, tämä foorumi vaatii tilin aktivointi. Käyttöavaimen on lähetetty e-mail osoite annoit. Tarkista e-mail lisätietoja';
$lang['ACCOUNT_ACTIVE'] = 'Tilisi on aktivoitu. Kiitos rekisteröitymisestä';
$lang['REACTIVATE'] = 'Aktivoida tilisi!';
$lang['ALREADY_ACTIVATED'] = 'Olet jo aktivoinut tilin';

$lang['REGISTRATION'] = 'Rekisteröinti Sopimuksen Ehdot';

$lang['WRONG_ACTIVATION'] = 'Aktivointi avain toimitetaan ei vastaa mitään tietokantaan.';
$lang['SEND_PASSWORD'] = 'Lähetä minulle uusi salasana';
$lang['PASSWORD_UPDATED'] = 'Uusi salasana on luotu; tarkista e-mail lisätietoja siitä, miten ottaa sen käyttöön.';
$lang['NO_EMAIL_MATCH'] = 'E-mail-osoite toimitetaan ei vastaa luettelossa, että käyttäjätunnus.';
$lang['NEW_PASSWORD_ACTIVATION'] = 'Uusi salasana aktivointi';
$lang['PASSWORD_ACTIVATED'] = 'Tili on uudelleen aktivoitu. Voit kirjautua sisään, käytä salasana toimitetaan e-mail olet saanut.';

$lang['SEND_EMAIL_MSG'] = 'Lähetä e-mail viesti';
$lang['NO_USER_SPECIFIED'] = 'Ei käyttäjä on määritetty';
$lang['USER_PREVENT_EMAIL'] = 'Tämä käyttäjä ei halua vastaanottaa sähköpostia. Yritä lähettää heille yksityisviesti.';
$lang['USER_NOT_EXIST'] = 'Että käyttäjää ei ole olemassa';
$lang['EMAIL_MESSAGE_DESC'] = 'Tämä viesti lähetetään pelkkänä tekstinä, joten eivät sisällä mitään HTML tai BBCode. Palautusosoite tämä viesti on asetettu your e-mail osoite.';
$lang['FLOOD_EMAIL_LIMIT'] = 'Et voi lähettää toisen sähköpostin, tällä kertaa. Yritä myöhemmin uudelleen.';
$lang['RECIPIENT'] = 'Vastaanottaja';
$lang['EMAIL_SENT'] = 'E-mail on lähetetty.';
$lang['SEND_EMAIL'] = 'Lähetä e-mail';
$lang['EMPTY_SUBJECT_EMAIL'] = 'Sinun täytyy määrittää, aihe e-mail.';
$lang['EMPTY_MESSAGE_EMAIL'] = 'Sinun täytyy kirjoittaa viestin sähköpostitse.';

$lang['USER_AGREEMENT'] = 'Käyttösopimus';
$lang['USER_AGREEMENT_HEAD'] = 'Jotta voit jatkaa, sinun on hyväksyttävä seuraavat säännöt';
$lang['USER_AGREEMENT_AGREE'] = 'Olen lukenut ja hyväksyn Käyttäjä sopimus edellä';

$lang['COPYRIGHT_HOLDERS'] = 'Tekijänoikeuksien haltijat';
$lang['ADVERT'] = 'Mainostaa tällä sivustolla';
$lang['NOT_FOUND'] = 'Tiedostoa ei löytynyt';

// Memberslist
$lang['SORT'] = 'Tavallaan';
$lang['SORT_TOP_TEN'] = 'Kymmenen Julisteita';
$lang['SORT_JOINED'] = 'Liittynyt Päivämäärä';
$lang['SORT_USERNAME'] = 'Käyttäjätunnus';
$lang['SORT_LOCATION'] = 'Sijainti';
$lang['SORT_POSTS'] = 'Viestejä yhteensä';
$lang['SORT_EMAIL'] = 'Sähköposti';
$lang['SORT_WEBSITE'] = 'Sivustolla';
$lang['ASC'] = 'Nouseva';
$lang['DESC'] = 'Laskeva';
$lang['ORDER'] = 'Jotta';

// Thanks
$lang['THANK_TOPIC'] = 'Vote for this topic';
$lang['THANKS_GRATITUDE'] = 'We appreciate your gratitude';
$lang['LAST_LIKES'] = 'Last votes';
$lang['LIKE_OWN_POST'] = 'You can\'t vote for your own topic';
$lang['NO_LIKES'] = 'Nobody gave a vote yet';
$lang['LIKE_ALREADY'] = 'You already voted this topic';

// Invites
$lang['INVITE_CODE'] = 'Invite code';
$lang['INCORRECT_INVITE'] = 'Invite not found';
$lang['INVITE_EXPIRED'] = 'Invite expired';

// Group control panel
$lang['GROUP_CONTROL_PANEL'] = 'Käyttäjän Ryhmät';
$lang['GROUP_CONFIGURATION'] = 'Ryhmän Kokoonpano';
$lang['GROUP_GOTO_CONFIG'] = 'Mennä Ryhmän Kokoonpano paneeli';
$lang['GROUP_RETURN'] = 'Palaa Käyttäjän Ryhmä sivu';
$lang['MEMBERSHIP_DETAILS'] = 'Ryhmän Jäsenyys Tiedot';
$lang['JOIN_A_GROUP'] = 'Liity Ryhmään';

$lang['GROUP_INFORMATION'] = 'Ryhmän Tiedot';
$lang['GROUP_NAME'] = 'Ryhmän nimi';
$lang['GROUP_DESCRIPTION'] = 'Ryhmän kuvaus';
$lang['GROUP_SIGNATURE'] = 'Ryhmä allekirjoitus';
$lang['GROUP_MEMBERSHIP'] = 'Ryhmän jäsenyys';
$lang['GROUP_MEMBERS'] = 'Ryhmän Jäsenet';
$lang['GROUP_MODERATOR'] = 'Ryhmän Moderaattori';
$lang['PENDING_MEMBERS'] = 'Vireillä Jäsenet';

$lang['GROUP_TIME'] = 'Luotu';
$lang['RELEASE_GROUP'] = 'Release Group';

$lang['GROUP_TYPE'] = 'Ryhmän tyyppi';
$lang['GROUP_OPEN'] = 'Avoin ryhmä';
$lang['GROUP_CLOSED'] = 'Suljettu ryhmä';
$lang['GROUP_HIDDEN'] = 'Piilotettu ryhmä';

$lang['GROUP_MEMBER_MOD'] = 'Ryhmän moderaattori';
$lang['GROUP_MEMBER_MEMBER'] = 'Nykyiset jäsenyydet';
$lang['GROUP_MEMBER_PENDING'] = 'Jäsenyydet vireillä';
$lang['GROUP_MEMBER_OPEN'] = 'Avoimiin ryhmiin';
$lang['GROUP_MEMBER_CLOSED'] = 'Suljetut ryhmät';
$lang['GROUP_MEMBER_HIDDEN'] = 'Piilotettu ryhmät';

$lang['NO_GROUPS_EXIST'] = 'Ryhmiä Ei Ole Olemassa';
$lang['GROUP_NOT_EXIST'] = 'Että käyttäjäryhmä ei ole olemassa';
$lang['NO_GROUP_ID_SPECIFIED'] = 'Ryhmän TUNNUS ei ole määritelty';

$lang['NO_GROUP_MEMBERS'] = 'Tässä ryhmässä ei ole jäseniä';
$lang['HIDDEN_GROUP_MEMBERS'] = 'Tämä ryhmä on piilotettu, et voi tarkastella sen jäsenyys';
$lang['NO_PENDING_GROUP_MEMBERS'] = 'Tämä ryhmä ei ole vireillä jäsenet';
$lang['GROUP_JOINED'] = 'Olet onnistuneesti tilannut tähän ryhmään.<br />You ilmoitetaan, kun tilaus on hyväksytty ryhmän moderaattori.';
$lang['GROUP_REQUEST'] = 'Pyynnön liittyä ryhmään on tehty.';
$lang['GROUP_APPROVED'] = 'Pyyntösi on hyväksytty.';
$lang['GROUP_ADDED'] = 'Sinut on lisätty tähän käyttäjäryhmään.';
$lang['ALREADY_MEMBER_GROUP'] = 'Sinulla on jo tämän ryhmän jäsen';
$lang['USER_IS_MEMBER_GROUP'] = '%s is already a member of this group';
$lang['USER_IS_MOD_GROUP'] = '%s is a moderator of this group';
$lang['GROUP_TYPE_UPDATED'] = 'Onnistuneesti päivitetty ryhmä tyyppi.';
$lang['EFFECTIVE_DATE'] = 'Voimaantulopäivä';

$lang['COULD_NOT_ADD_USER'] = 'Käyttäjä olet valinnut ei ole olemassa.';
$lang['COULD_NOT_ANON_USER'] = 'Et voi tehdä Nimettömänä ryhmän jäsen.';

$lang['CONFIRM_UNSUB'] = 'Oletko varma, että haluat peruuttaa tämän ryhmän?';
$lang['CONFIRM_UNSUB_PENDING'] = 'Tilauksen tämä ryhmä ei ole vielä hyväksytty; oletko varma, että haluat lopettaa?';

$lang['UNSUB_SUCCESS'] = 'Sinulla on ollut yk: n-merkityn tästä ryhmästä.';

$lang['APPROVE_SELECTED'] = 'Hyväksy Valitut';
$lang['DENY_SELECTED'] = 'Kieltää Valittu';
$lang['NOT_LOGGED_IN'] = 'Sinun täytyy olla kirjautuneena liittyä ryhmään.';
$lang['REMOVE_SELECTED'] = 'Poista Valitut';
$lang['ADD_MEMBER'] = 'Lisää Jäsen';
$lang['NOT_GROUP_MODERATOR'] = 'Et ole tämän ryhmän moderaattori, siksi ei voi suorittaa toimenpidettä.';

$lang['LOGIN_TO_JOIN'] = 'Kirjaudu sisään liity tai hallita ryhmän jäsenyyksiä';
$lang['THIS_OPEN_GROUP'] = 'Tämä on avoin ryhmä: valitse pyytää jäsenyyttä';
$lang['THIS_CLOSED_GROUP'] = 'Tämä on suljettu ryhmä: ei enää käyttäjät hyväksytty';
$lang['THIS_HIDDEN_GROUP'] = 'Tämä on piilotettu ryhmä: automaattinen käyttäjän lisäksi ei ole sallittua';
$lang['MEMBER_THIS_GROUP'] = 'Olet ryhmän jäsen';
$lang['PENDING_THIS_GROUP'] = 'Jäsenyytesi tässä ryhmässä on vireillä';
$lang['ARE_GROUP_MODERATOR'] = 'Olet ryhmän moderaattori';
$lang['NONE'] = 'Ei mitään';

$lang['SUBSCRIBE'] = 'Tilata';
$lang['UNSUBSCRIBE_GROUP'] = 'Sivustoa';
$lang['VIEW_INFORMATION'] = 'Näytä Tiedot';
$lang['MEMBERS_IN_GROUP'] = 'Jäseniä ryhmässä';

// Release Groups
$lang['POST_RELEASE_FROM_GROUP'] = 'Post vapauttaa ryhmä';
$lang['CHOOSE_RELEASE_GROUP'] = 'ei valittu';
$lang['ATTACH_RG_SIG'] = 'kiinnitä release ryhmä allekirjoitus';
$lang['RELEASE_FROM_RG'] = 'Julkaisu oli laatinut';
$lang['GROUPS_RELEASES'] = 'Konsernin julkaisut';
$lang['MORE_RELEASES'] = 'Etsi kaikki tiedotteet ryhmä';
$lang['NOT_A_RELEASE_GROUP'] = 'Tämä ryhmä ei ole release-ryhmä';

// Search
$lang['SEARCH_OFF'] = 'Haku on väliaikaisesti poissa käytöstä';
$lang['SEARCH_ERROR'] = 'Tällä hetkellä hakukone ei ole available<br /><br />Try toistaa pyynnön jälkeen muutaman sekunnin';
$lang['SEARCH_HELP_URL'] = 'Haku Auttaa';
$lang['SEARCH_QUERY'] = 'Kyselyn';
$lang['SEARCH_OPTIONS'] = 'Haku Vaihtoehtoja';

$lang['SEARCH_WORDS'] = 'Etsi Avainsanoja';
$lang['SEARCH_WORDS_EXPL'] = 'Voit käyttää <b>+</b> määrittämään sanat joiden täytyy olla tuloksia ja <b>-</b> määritellä sanoja, joita ei pitäisi olla tulos (ex: "+sana1 -sana2"). Käyttää * - merkkiä yleismerkkinä osittaista ottelut';
$lang['SEARCH_AUTHOR'] = 'Etsi Kirjailija';
$lang['SEARCH_AUTHOR_EXPL'] = 'Käyttää * - merkkiä yleismerkkinä osittaista ottelut';

$lang['SEARCH_TITLES_ONLY'] = 'Etsi aihe otsikot vain';
$lang['SEARCH_ALL_WORDS'] = 'kaikki sanat';
$lang['SEARCH_MY_MSG_ONLY'] = 'Etsi vain minun virkaa';
$lang['IN_MY_POSTS'] = 'Minun virkaa';
$lang['SEARCH_MY_TOPICS'] = 'minun aiheita';
$lang['NEW_TOPICS'] = 'Uusia aiheita';

$lang['RETURN_FIRST'] = 'Palaa ensin'; // followed by xxx characters in a select box
$lang['CHARACTERS_POSTS'] = 'merkkiä viestit';

$lang['SEARCH_PREVIOUS'] = 'Etsi edellinen';

$lang['SORT_BY'] = 'Lajittele';
$lang['SORT_TIME'] = 'Post Aikaa';
$lang['SORT_POST_SUBJECT'] = 'Viesti Aihe';
$lang['SORT_TOPIC_TITLE'] = 'Aiheen Otsikko';
$lang['SORT_AUTHOR'] = 'Kirjoittaja';
$lang['SORT_FORUM'] = 'Forum';

$lang['DISPLAY_RESULTS_AS'] = 'Näyttää tulokset';
$lang['ALL_AVAILABLE'] = 'Kaikki saatavilla';
$lang['BRIEFLY'] = 'Lyhyesti';
$lang['NO_SEARCHABLE_FORUMS'] = 'Sinulla ei ole oikeuksia etsiä tahansa foorumilla tällä sivustolla.';

$lang['NO_SEARCH_MATCH'] = 'Ei aiheita tai virkaa tavannut hakuehdot';
$lang['FOUND_SEARCH_MATCH'] = 'Haku löysi %d ottelu'; // e.g. Search found 1 match
$lang['FOUND_SEARCH_MATCHES'] = 'Haku löysi %d ottelut'; // e.g. Search found 24 matches
$lang['TOO_MANY_SEARCH_RESULTS'] = 'Liian monet tulokset voivat olla löytynyt, yritä olla tarkempi';

$lang['CLOSE_WINDOW'] = 'Sulje Ikkuna';
$lang['CLOSE'] = 'lähellä';
$lang['HIDE'] = 'piilota';
$lang['SEARCH_TERMS'] = 'Hakusanoja';

// Auth related entries
// Note the %s will be replaced with one of the following 'user' arrays
$lang['SORRY_AUTH_VIEW'] = 'Anteeksi, mutta vain %s voi tarkastella tällä foorumilla.';
$lang['SORRY_AUTH_READ'] = 'Anteeksi, mutta vain %s voivat lukea aiheita tässä foorumissa.';
$lang['SORRY_AUTH_POST'] = 'Anteeksi, mutta vain %s voi lähettää aiheita tällä foorumilla.';
$lang['SORRY_AUTH_REPLY'] = 'Anteeksi, mutta vain %s voi vastata viesteihin tässä foorumissa.';
$lang['SORRY_AUTH_EDIT'] = 'Anteeksi, mutta vain %s voi muokata viestejäsi tässä foorumissa.';
$lang['SORRY_AUTH_DELETE'] = 'Anteeksi, mutta vain %s voi poistaa viestejä tällä foorumilla.';
$lang['SORRY_AUTH_VOTE'] = 'Anteeksi, mutta vain %s voi äänestää tässä foorumissa.';
$lang['SORRY_AUTH_STICKY'] = 'Anteeksi, mutta vain %s voi lähettää sticky viestejä tällä foorumilla.';
$lang['SORRY_AUTH_ANNOUNCE'] = 'Anteeksi, mutta vain %s voivat lähettää ilmoitukset tässä foorumissa.';

// These replace the %s in the above strings
$lang['AUTH_ANONYMOUS_USERS'] = '<b>anonymous users</b>';
$lang['AUTH_REGISTERED_USERS'] = '<b>registered users</b>';
$lang['AUTH_USERS_GRANTED_ACCESS'] = '<b>users myönnetty erityinen access</b>';
$lang['AUTH_MODERATORS'] = '<b>moderators</b>';
$lang['AUTH_ADMINISTRATORS'] = '<b>administrators</b>';

$lang['NOT_MODERATOR'] = 'Et ole moderaattori tällä foorumilla.';
$lang['NOT_AUTHORISED'] = 'Ei Ole Sallittua';

$lang['YOU_BEEN_BANNED'] = 'Sinut on kielletty tästä foorumista. Ota yhteyttä ylläpitäjään lisätietoa.';

// Viewonline
$lang['ONLINE_EXPLAIN'] = 'aktiiviset käyttäjät viimeisen viiden minuutin';
$lang['LAST_UPDATED'] = 'Viimeksi Päivitetty';

// Moderator Control Panel
$lang['MOD_CP'] = 'Moderaattori Ohjauspaneeli';
$lang['MOD_CP_EXPLAIN'] = 'Alla olevalla lomakkeella voit tehdä massa maltillisesti toimintaa tällä foorumilla. Voit lukita, avata, siirtää tai poistaa useita aiheita.';

$lang['SELECT'] = 'Valitse';
$lang['DELETE'] = 'Poista';
$lang['MOVE'] = 'Siirrä';
$lang['LOCK'] = 'Lukko';
$lang['UNLOCK'] = 'Avata';

$lang['TOPICS_REMOVED'] = 'Valitut aiheet ovat olleet onnistuneesti poistaa tietokannasta.';
$lang['NO_TOPICS_REMOVED'] = 'Ei aiheita poistettiin.';
$lang['TOPICS_LOCKED'] = 'Valitut aiheet ovat olleet lukossa.';
$lang['TOPICS_MOVED'] = 'Valitut aiheet on siirretty.';
$lang['TOPICS_UNLOCKED'] = 'Valitut aiheet ovat olleet auki.';
$lang['NO_TOPICS_MOVED'] = 'Ei aiheita siirrettiin.';

$lang['CONFIRM_DELETE_TOPIC'] = 'Oletko varma, että haluat poistaa valitun aiheen/s?';
$lang['CONFIRM_LOCK_TOPIC'] = 'Oletko varma, että haluat lukita valitun aiheen/s?';
$lang['CONFIRM_UNLOCK_TOPIC'] = 'Oletko varma, että haluat avata valitun aiheen/s?';
$lang['CONFIRM_MOVE_TOPIC'] = 'Oletko varma, että haluat siirtää valitun aiheen/s?';

$lang['MOVE_TO_FORUM'] = 'Siirrä forum';
$lang['LEAVE_SHADOW_TOPIC'] = 'Jätä varjo aihe vanha foorumi.';

$lang['SPLIT_TOPIC_EXPLAIN'] = 'Alla olevalla lomakkeella voit jakaa aiheen kahteen, joko valitsemalla viestit erikseen tai jakamalla valitun post';
$lang['NEW_TOPIC_TITLE'] = 'Uuden aiheen otsikko';
$lang['FORUM_FOR_NEW_TOPIC'] = 'Foorumin uusi aihe';
$lang['SPLIT_POSTS'] = 'Jakaa valitut viestit';
$lang['SPLIT_AFTER'] = 'Split alkaen valittu virkaan';
$lang['TOPIC_SPLIT'] = 'Valittu aihe on jaettu onnistuneesti';

$lang['TOO_MANY_ERROR'] = 'Olet valinnut liian monta viestiä. Voit valita vain yhden post jakaa aiheen jälkeen!';

$lang['NONE_SELECTED'] = 'Sinulla ei valittu suorittamaan tämän operaation. Mene takaisin ja valitse vähintään yksi.';
$lang['NEW_FORUM'] = 'Uusi foorumi';

$lang['THIS_POSTS_IP'] = 'IP-osoite tämä viesti';
$lang['OTHER_IP_THIS_USER'] = 'Muut IP-osoitteet käyttäjä on lähetetty';
$lang['USERS_THIS_IP'] = 'Käyttäjät lähettämistä tästä IP-osoite';
$lang['IP_INFO'] = 'IP-Tietoja';
$lang['LOOKUP_IP'] = 'Katso ylös IP-osoite';

// Timezones ... for display on each page
$lang['ALL_TIMES'] = 'Kaikki ajat ovat <span class="tz_time">%s</span>'; // This is followed by UTC and the timezone offset

// These are displayed in the timezone select box
$lang['TZ']['-12'] = 'UTC - 12';
$lang['TZ']['-11'] = 'UTC - 11';
$lang['TZ']['-10'] = 'UTC - 10';
$lang['TZ']['-9'] = 'UTC - 9';
$lang['TZ']['-8'] = 'UTC - 8';
$lang['TZ']['-7'] = 'UTC - 7';
$lang['TZ']['-6'] = 'UTC - 6';
$lang['TZ']['-5'] = 'UTC - 5';
$lang['TZ']['-4'] = 'UTC - 4';
$lang['TZ']['-3.5'] = 'UTC - 3.5';
$lang['TZ']['-3'] = 'UTC - 3';
$lang['TZ']['-2'] = 'UTC - 2';
$lang['TZ']['-1'] = 'UTC - 1';
$lang['TZ']['0'] = 'UTC ± 0';
$lang['TZ']['1'] = 'UTC + 1';
$lang['TZ']['2'] = 'UTC + 2';
$lang['TZ']['3'] = 'UTC + 3';
$lang['TZ']['3.5'] = 'UTC + 3.5';
$lang['TZ']['4'] = 'UTC + 4';
$lang['TZ']['4.5'] = 'UTC + 4.5';
$lang['TZ']['5'] = 'UTC + 5';
$lang['TZ']['5.5'] = 'UTC + 5.5';
$lang['TZ']['6'] = 'UTC + 6';
$lang['TZ']['6.5'] = 'UTC + 6.5';
$lang['TZ']['7'] = 'UTC + 7';
$lang['TZ']['8'] = 'UTC + 8';
$lang['TZ']['9'] = 'UTC + 9';
$lang['TZ']['9.5'] = 'UTC + 9.5';
$lang['TZ']['10'] = 'UTC + 10';
$lang['TZ']['11'] = 'UTC + 11';
$lang['TZ']['12'] = 'UTC + 12';
$lang['TZ']['13'] = 'UTC + 13';

$lang['DATETIME']['TODAY'] = 'Tänään';
$lang['DATETIME']['YESTERDAY'] = 'Eilen';

$lang['DATETIME']['SUNDAY'] = 'Sunnuntaina';
$lang['DATETIME']['MONDAY'] = 'Maanantaina';
$lang['DATETIME']['TUESDAY'] = 'Tiistaina';
$lang['DATETIME']['WEDNESDAY'] = 'Keskiviikkona';
$lang['DATETIME']['THURSDAY'] = 'Torstaina';
$lang['DATETIME']['FRIDAY'] = 'Perjantaina';
$lang['DATETIME']['SATURDAY'] = 'Lauantaina';
$lang['DATETIME']['SUN'] = 'Aurinko';
$lang['DATETIME']['MON'] = 'Mon';
$lang['DATETIME']['TUE'] = 'Ti';
$lang['DATETIME']['WED'] = 'Ke';
$lang['DATETIME']['THU'] = 'Thu';
$lang['DATETIME']['FRI'] = 'Pe';
$lang['DATETIME']['SAT'] = 'Sat';
$lang['DATETIME']['JANUARY'] = 'Tammikuuta';
$lang['DATETIME']['FEBRUARY'] = 'Helmikuuta';
$lang['DATETIME']['MARCH'] = 'Maaliskuussa';
$lang['DATETIME']['APRIL'] = 'Huhtikuuta';
$lang['DATETIME']['MAY'] = 'Voi';
$lang['DATETIME']['JUNE'] = 'Kesäkuussa';
$lang['DATETIME']['JULY'] = 'Heinäkuuta';
$lang['DATETIME']['AUGUST'] = 'Elokuussa';
$lang['DATETIME']['SEPTEMBER'] = 'Syyskuussa';
$lang['DATETIME']['OCTOBER'] = 'Lokakuussa';
$lang['DATETIME']['NOVEMBER'] = 'Marraskuuta';
$lang['DATETIME']['DECEMBER'] = 'Joulukuuta';
$lang['DATETIME']['JAN'] = 'Jan';
$lang['DATETIME']['FEB'] = 'Helmikuu';
$lang['DATETIME']['MAR'] = 'Mar';
$lang['DATETIME']['APR'] = 'Apr';
$lang['DATETIME']['JUN'] = 'Jun';
$lang['DATETIME']['JUL'] = 'Jul';
$lang['DATETIME']['AUG'] = 'Aug';
$lang['DATETIME']['SEP'] = 'Sep';
$lang['DATETIME']['OCT'] = 'Mma';
$lang['DATETIME']['NOV'] = 'Nov';
$lang['DATETIME']['DEC'] = 'Dec';

// Country selector
$lang['COUNTRY'] = 'Country';
$lang['SET_OWN_COUNTRY'] = 'Set own country (Manually)';
$lang['COUNTRIES'] = [
    0 => 'Ei valitse',
    'AD' => 'Andorra',
    'AE' => 'United Arab Emirates',
    'AF' => 'Afghanistan',
    'AG' => 'Antigua and Barbuda',
    'AI' => 'Anguilla',
    'AL' => 'Albania',
    'AM' => 'Armenia',
    'AO' => 'Angola',
    'AQ' => 'Antarctica',
    'AR' => 'Argentina',
    'AS' => 'American Samoa',
    'AT' => 'Austria',
    'AU' => 'Australia',
    'AW' => 'Aruba',
    'AX' => 'Aland Islands',
    'AZ' => 'Azerbaijan',
    'BA' => 'Bosnia and Herzegovina',
    'BB' => 'Barbados',
    'BD' => 'Bangladesh',
    'BE' => 'Belgium',
    'BF' => 'Burkina Faso',
    'BG' => 'Bulgaria',
    'BH' => 'Bahrain',
    'BI' => 'Burundi',
    'BJ' => 'Benin',
    'BL' => 'Saint Barthélemy',
    'BM' => 'Bermuda',
    'BN' => 'Brunei Darussalam',
    'BO' => 'Bolivia, Plurinational State of',
    'BQ' => 'Caribbean Netherlands',
    'BR' => 'Brazil',
    'BS' => 'Bahamas',
    'BT' => 'Bhutan',
    'BV' => 'Bouvet Island',
    'BW' => 'Botswana',
    'BY' => 'Belarus',
    'BZ' => 'Belize',
    'CA' => 'Canada',
    'CC' => 'Cocos (Keeling) Islands',
    'CD' => 'Congo, the Democratic Republic of the',
    'CF' => 'Central African Republic',
    'CG' => 'Republic of the Congo',
    'CH' => 'Switzerland',
    'CI' => 'Republic of Cote d\'Ivoire',
    'CK' => 'Cook Islands',
    'CL' => 'Chile',
    'CM' => 'Cameroon',
    'CN' => 'China (People\'s Republic of China)',
    'CO' => 'Colombia',
    'CR' => 'Costa Rica',
    'CU' => 'Cuba',
    'CV' => 'Cape Verde',
    'CW' => 'Country of Curaçao',
    'CX' => 'Christmas Island',
    'CY' => 'Cyprus',
    'CZ' => 'Czech Republic',
    'DE' => 'Germany',
    'DJ' => 'Djibouti',
    'DK' => 'Denmark',
    'DM' => 'Dominica',
    'DO' => 'Dominican Republic',
    'DZ' => 'Algeria',
    'EC' => 'Ecuador',
    'EE' => 'Estonia',
    'EG' => 'Egypt',
    'EH' => 'Western Sahara',
    'ER' => 'Eritrea',
    'ES' => 'Spain',
    'ET' => 'Ethiopia',
    'EU' => 'Europe',
    'FI' => 'Finland',
    'FJ' => 'Fiji',
    'FK' => 'Falkland Islands (Malvinas)',
    'FM' => 'Micronesia, Federated States of',
    'FO' => 'Faroe Islands',
    'FR' => 'France',
    'GA' => 'Gabon',
    'GB-ENG' => 'England',
    'GB-NIR' => 'Northern Ireland',
    'GB-SCT' => 'Scotland',
    'GB-WLS' => 'Wales',
    'GB' => 'United Kingdom',
    'GD' => 'Grenada',
    'GE' => 'Georgia',
    'GF' => 'French Guiana',
    'GG' => 'Guernsey',
    'GH' => 'Ghana',
    'GI' => 'Gibraltar',
    'GL' => 'Greenland',
    'GM' => 'Gambia',
    'GN' => 'Guinea',
    'GP' => 'Guadeloupe',
    'GQ' => 'Equatorial Guinea',
    'GR' => 'Greece',
    'GS' => 'South Georgia and the South Sandwich Islands',
    'GT' => 'Guatemala',
    'GU' => 'Guam',
    'GW' => 'Guinea-Bissau',
    'GY' => 'Guyana',
    'HK' => 'Hong Kong',
    'HM' => 'Heard Island and McDonald Islands',
    'HN' => 'Honduras',
    'HR' => 'Croatia',
    'HT' => 'Haiti',
    'HU' => 'Hungary',
    'ID' => 'Indonesia',
    'IE' => 'Ireland',
    'IL' => 'Israel',
    'IM' => 'Isle of Man',
    'IN' => 'India',
    'IO' => 'British Indian Ocean Territory',
    'IQ' => 'Iraq',
    'IR' => 'Iran, Islamic Republic of',
    'IS' => 'Iceland',
    'IT' => 'Italy',
    'JE' => 'Jersey',
    'JM' => 'Jamaica',
    'JO' => 'Jordan',
    'JP' => 'Japan',
    'KE' => 'Kenya',
    'KG' => 'Kyrgyzstan',
    'KH' => 'Cambodia',
    'KI' => 'Kiribati',
    'KM' => 'Comoros',
    'KN' => 'Saint Kitts and Nevis',
    'KP' => 'Korea, Democratic People\'s Republic of',
    'KR' => 'Korea, Republic of',
    'KW' => 'Kuwait',
    'KY' => 'Cayman Islands',
    'KZ' => 'Kazakhstan',
    'LA' => 'Laos (Lao People\'s Democratic Republic)',
    'LB' => 'Lebanon',
    'LC' => 'Saint Lucia',
    'LI' => 'Liechtenstein',
    'LK' => 'Sri Lanka',
    'LR' => 'Liberia',
    'LS' => 'Lesotho',
    'LT' => 'Lithuania',
    'LU' => 'Luxembourg',
    'LV' => 'Latvia',
    'LY' => 'Libya',
    'MA' => 'Morocco',
    'MC' => 'Monaco',
    'MD' => 'Moldova, Republic of',
    'ME' => 'Montenegro',
    'MF' => 'Saint Martin',
    'MG' => 'Madagascar',
    'MH' => 'Marshall Islands',
    'MK' => 'North Macedonia',
    'ML' => 'Mali',
    'MM' => 'Myanmar',
    'MN' => 'Mongolia',
    'MO' => 'Macao',
    'MP' => 'Northern Mariana Islands',
    'MQ' => 'Martinique',
    'MR' => 'Mauritania',
    'MS' => 'Montserrat',
    'MT' => 'Malta',
    'MU' => 'Mauritius',
    'MV' => 'Maldives',
    'MW' => 'Malawi',
    'MX' => 'Mexico',
    'MY' => 'Malaysia',
    'MZ' => 'Mozambique',
    'NA' => 'Namibia',
    'NC' => 'New Caledonia',
    'NE' => 'Niger',
    'NF' => 'Norfolk Island',
    'NG' => 'Nigeria',
    'NI' => 'Nicaragua',
    'NL' => 'Netherlands',
    'NO' => 'Norway',
    'NP' => 'Nepal',
    'NR' => 'Nauru',
    'NU' => 'Niue',
    'NZ' => 'New Zealand',
    'OM' => 'Oman',
    'PA' => 'Panama',
    'PE' => 'Peru',
    'PF' => 'French Polynesia',
    'PG' => 'Papua New Guinea',
    'PH' => 'Philippines',
    'PK' => 'Pakistan',
    'PL' => 'Poland',
    'PM' => 'Saint Pierre and Miquelon',
    'PN' => 'Pitcairn',
    'PR' => 'Puerto Rico',
    'PS' => 'Palestine',
    'PT' => 'Portugal',
    'PW' => 'Palau',
    'PY' => 'Paraguay',
    'QA' => 'Qatar',
    'RE' => 'Réunion',
    'RO' => 'Romania',
    'RS' => 'Serbia',
    'RU' => 'Russian Federation',
    'RW' => 'Rwanda',
    'SA' => 'Saudi Arabia',
    'SB' => 'Solomon Islands',
    'SC' => 'Seychelles',
    'SD' => 'Sudan',
    'SE' => 'Sweden',
    'SG' => 'Singapore',
    'SH' => 'Saint Helena, Ascension and Tristan da Cunha',
    'SI' => 'Slovenia',
    'SJ' => 'Svalbard and Jan Mayen Islands',
    'SK' => 'Slovakia',
    'SL' => 'Sierra Leone',
    'SM' => 'San Marino',
    'SN' => 'Senegal',
    'SO' => 'Somalia',
    'SR' => 'Suriname',
    'SS' => 'South Sudan',
    'SU' => 'Soviet Union',
    'ST' => 'Sao Tome and Principe',
    'SV' => 'El Salvador',
    'SX' => 'Sint Maarten (Dutch part)',
    'SY' => 'Syrian Arab Republic',
    'SZ' => 'Swaziland',
    'TC' => 'Turks and Caicos Islands',
    'TD' => 'Chad',
    'TF' => 'French Southern Territories',
    'TG' => 'Togo',
    'TH' => 'Thailand',
    'TJ' => 'Tajikistan',
    'TK' => 'Tokelau',
    'TL' => 'Timor-Leste',
    'TM' => 'Turkmenistan',
    'TN' => 'Tunisia',
    'TO' => 'Tonga',
    'TR' => 'Turkey',
    'TT' => 'Trinidad and Tobago',
    'TV' => 'Tuvalu',
    'TW' => 'Taiwan (Republic of China)',
    'TZ' => 'Tanzania, United Republic of',
    'UA' => 'Ukraine',
    'UG' => 'Uganda',
    'UM' => 'US Minor Outlying Islands',
    'US' => 'United States',
    'UY' => 'Uruguay',
    'UZ' => 'Uzbekistan',
    'VA' => 'Holy See (Vatican City State)',
    'VC' => 'Saint Vincent and the Grenadines',
    'VE' => 'Venezuela, Bolivarian Republic of',
    'VG' => 'Virgin Islands, British',
    'VI' => 'Virgin Islands, U.S.',
    'VN' => 'Vietnam',
    'VU' => 'Vanuatu',
    'WF' => 'Wallis and Futuna Islands',
    'WS' => 'Samoa',
    'XK' => 'Kosovo',
    'YE' => 'Yemen',
    'YU' => 'Yugoslavia',
    'YT' => 'Mayotte',
    'ZA' => 'South Africa',
    'ZM' => 'Zambia',
    'ZW' => 'Zimbabwe',
    // Additional flags
    'WBW' => 'Wonderful Russia of the Future',
    'PACE' => 'Peace flag',
    'LGBT' => 'Pride flag'
];

// Errors
$lang['INFORMATION'] = 'Tiedot';
$lang['ADMIN_REAUTHENTICATE'] = 'Hallinnoida/kohtalainen aluksella, sinun täytyy todentaa itsensä uudelleen itse.';

// Attachment Mod Main Language Variables
// Auth Related Entries
$lang['RULES_ATTACH_CAN'] = 'Sinun <b>can</b> liittää tiedostoja tässä foorumissa';
$lang['RULES_ATTACH_CANNOT'] = 'Sinun <b>cannot</b> liittää tiedostoja tässä foorumissa';
$lang['RULES_DOWNLOAD_CAN'] = 'Sinun <b>can</b> ladata tiedostoja tässä foorumissa';
$lang['RULES_DOWNLOAD_CANNOT'] = 'Sinun <b>cannot</b> ladata tiedostoja tässä foorumissa';
$lang['SORRY_AUTH_VIEW_ATTACH'] = 'Anteeksi, mutta sinulla ei ole oikeutta katsella tai ladata tämän Liitetiedoston';

// Viewtopic -> Display of Attachments
$lang['DESCRIPTION'] = 'Kuvaus'; // used in Administration Panel too...
$lang['DOWNLOAD'] = 'Lataa'; // this Language Variable is defined in admin.php too, but we are unable to access it from the main Language File
$lang['FILESIZE'] = 'Filesize';
$lang['VIEWED'] = 'Katsella';
$lang['EXTENSION_DISABLED_AFTER_POSTING'] = 'Tiedostopääte on \'%s\' oli pois päältä, jonka board admin, siksi tämä Kiinnitys ei näy.'; // used in Posts and PM's, replace %s with mime type

// Viewtopic -> Display of Attachments -> TorrServer integration
$lang['STREAM'] = 'Stream';
$lang['RESOLUTION'] = 'Resolution: <b>%s</b>';
$lang['CHANNELS'] = 'Channels: <b>%s</b>';
$lang['CHANNELS_LAYOUT'] = 'Channels layout: <b>%s</b>';
$lang['BITRATE'] = 'Bitrate: <b>%s</b>';
$lang['SAMPLE_RATE'] = 'Sample rate: <b>%s</b>';
$lang['AUDIO_TRACK'] = 'Audio track information (%d):';
$lang['AUDIO_CODEC'] = 'Audio codec: <b title="%s">%s</b>';
$lang['VIDEO_CODEC'] = 'Video codec: <b title="%s">%s</b>';
$lang['SHOW_MORE_INFORMATION_FILE'] = 'Show more information about file';
$lang['DOWNLOAD_M3U_FILE'] = 'Download .m3u file';
$lang['PLAYBACK_M3U'] = 'Playback .m3u file';
$lang['COPY_STREAM_LINK'] = 'Copy stream link to clipboard';
$lang['M3U_NOT_SUPPORTED'] = 'This file cannot be played in the browser...';
$lang['M3U_FFPROBE_NO_DATA'] = 'It seems ffprobe will not be able to return data about this codec...';
$lang['M3U_NOTICE'] = 'Some browsers do not support playback of certain video formats. In such a case, you can download the .m3u file and play it using a third-party player';

$lang['ATTACHMENT'] = 'Liitteet';
$lang['ATTACHMENT_THUMBNAIL'] = 'Kiinnitys Thumbnail';

// Posting/PM -> Posting Attachments
$lang['ADD_ATTACHMENT'] = 'Lisää Liite';
$lang['ADD_ATTACHMENT_TITLE'] = 'Lisää Liite';
$lang['ADD_ATTACHMENT_EXPLAIN'] = 'Jos et halua lisätä Liitetiedoston viestiisi, jätä Kentät tyhjiksi';
$lang['FILENAME'] = 'Tiedostonimi';
$lang['FILE_COMMENT'] = 'Tiedoston Kommentti';

// Posting/PM -> Posted Attachments
$lang['POSTED_ATTACHMENTS'] = 'Lähetetty Liitteet';
$lang['UPDATE_COMMENT'] = 'Päivitys Kommentti';
$lang['DELETE_ATTACHMENTS'] = 'Poistaa Liitetiedostoja';
$lang['DELETE_ATTACHMENT'] = 'Poista Kiinnitys';
$lang['DELETE_THUMBNAIL'] = 'Poista Pikkukuva';
$lang['UPLOAD_NEW_VERSION'] = 'Lataa Uusi Versio';

// Errors -> Posting Attachments
$lang['INVALID_FILENAME'] = '%s on virheellinen tiedostonimi'; // replace %s with given filename
$lang['ATTACHMENT_PHP_SIZE_NA'] = 'Liite on liian iso.<br />Could saa maksimikoko määritellään PHP.<br />The Kiinnitys Mod ei pysty määrittämään maksimi lähetyskoko on määritelty php.ini.';
$lang['ATTACHMENT_PHP_SIZE_OVERRUN'] = 'Liite on liian iso.<br />Maximum Lähetyksen Koko: %d MB.<br />Please huomaa, että tämä Koko on määritelty php.ini, tämä tarkoittaa, että se on asetettu PHP ja Kiinnitys Mod voi ohittaa tämän arvon.'; // replace %d with ini_get('upload_max_filesize')
$lang['DISALLOWED_EXTENSION'] = 'Laajennus %s ei ole sallittua'; // replace %s with extension (e.g. .php)
$lang['DISALLOWED_EXTENSION_WITHIN_FORUM'] = 'Sinulla ei ole oikeutta lähettää Tiedostoja Laajennus %s Foorumissa'; // replace %s with the Extension
$lang['ATTACHMENT_TOO_BIG'] = 'The Attachment is too big.<br />Max Size: %s'; // replace %d with maximum file size, %s with size var
$lang['ATTACH_QUOTA_REACHED'] = 'Anteeksi, mutta suurin tiedostokoko kaikki Liitteet on saavutettu. Ota yhteyttä ylläpitäjään, jos sinulla on kysyttävää.';
$lang['TOO_MANY_ATTACHMENTS'] = 'Liitettä ei voi lisätä, koska max. määrä %d Liitetiedostoja tämä viesti oli saavutettu'; // replace %d with maximum number of attachments
$lang['ERROR_IMAGESIZE'] = 'Liitetiedosto/Kuvan tulee olla vähemmän kuin %d pikseliä leveä ja %d pikseliä korkea';
$lang['GENERAL_UPLOAD_ERROR'] = 'Ladata Virhe: ei voitu ladata Liitetiedoston %s.'; // replace %s with local path

$lang['ERROR_EMPTY_ADD_ATTACHBOX'] = 'Sinun täytyy syöttää arvot "Lisää Liite" - Ruutuun';
$lang['ERROR_MISSING_OLD_ENTRY'] = 'Voi Päivittää Kiinnitys, ei löytänyt vanha Kiinnitys Merkintä';

// Errors -> PM Related
$lang['ATTACH_QUOTA_SENDER_PM_REACHED'] = 'Anteeksi, mutta suurin tiedostokoko kaikille Liitetiedostoja teidän Yksityinen Viesti Kansio on saavutettu. Poista joitakin saadut/lähetetyt Liitteet.';
$lang['ATTACH_QUOTA_RECEIVER_PM_REACHED'] = 'Anteeksi, mutta suurin tiedostokoko kaikille Liitteet Yksityinen Viesti Kansioon \'%s\' on saavutettu. Anna hänen tietää, tai odottaa, kunnes hän/hän on poistanut joitakin hänen/hänen Liitteet.';

// Errors -> Download
$lang['NO_ATTACHMENT_SELECTED'] = 'Et ole valinnut liitetiedostona ladata tai katsoa.';
$lang['ERROR_NO_ATTACHMENT'] = 'Valitun Kiinnitys ei enää ole olemassa';

// Delete Attachments
$lang['CONFIRM_DELETE_ATTACHMENTS'] = 'Oletko varma, että haluat poistaa valitun Liitetiedostoja?';
$lang['DELETED_ATTACHMENTS'] = 'Valitut Liitteet on poistettu.';
$lang['ERROR_DELETED_ATTACHMENTS'] = 'Ei voitu poistaa Liitetiedostoja.';
$lang['CONFIRM_DELETE_PM_ATTACHMENTS'] = 'Oletko varma, että haluat poistaa kaikki Liitteet lähetetty tämä PM?';

// General Error Messages
$lang['ATTACHMENT_FEATURE_DISABLED'] = 'Liitetiedosto-Ominaisuus on poistettu käytöstä.';

$lang['DIRECTORY_DOES_NOT_EXIST'] = 'Hakemisto \'%s\' ei ole olemassa tai ei löytynyt.'; // replace %s with directory
$lang['DIRECTORY_IS_NOT_A_DIR'] = 'Ole hyvä ja tarkista, jos %s\' on hakemisto.'; // replace %s with directory
$lang['DIRECTORY_NOT_WRITEABLE'] = 'Hakemisto \'%s\' ei voi kirjoittaa. Sinun täytyy luoda upload polku ja chmod se 777 (tai vaihtaa omistajaa sinulle httpd-palvelimet omistaja) ladata tiedostoja.<br />If sinulla on vain tavallinen FTP-yhteys muuttaa \'Ominaisuus\' hakemiston rwxrwxrwx.'; // replace %s with directory

// Quota Variables
$lang['UPLOAD_QUOTA'] = 'Lähetyksen Kiintiön';
$lang['PM_QUOTA'] = 'PM Kiintiön';

// Common Variables
$lang['BYTES'] = 'Tavua';
$lang['KB'] = 'KB';
$lang['MB'] = 'MB';
$lang['GB'] = 'GT';
$lang['ATTACH_SEARCH_QUERY'] = 'Etsi Liitetiedostoja';
$lang['TEST_SETTINGS'] = 'Testin Asetukset';
$lang['NOT_ASSIGNED'] = 'Ei Määritetty';
$lang['NO_FILE_COMMENT_AVAILABLE'] = 'Ei Tiedoston Kommentti saatavilla';
$lang['ATTACHBOX_LIMIT'] = 'Sinun Attachbox is<br /><b>%d%%</b> täynnä';
$lang['NO_QUOTA_LIMIT'] = 'Ei Quota Limit';
$lang['UNLIMITED'] = 'Rajoittamaton';

//bt
$lang['BT_REG_YES'] = 'Rekisteröity';
$lang['BT_REG_NO'] = 'Ei ole rekisteröity';
$lang['BT_ADDED'] = 'Lisätty';
$lang['BT_REG_ON_TRACKER'] = 'Rekisteröityä tracker';
$lang['BT_REG_FAIL'] = 'Ei voitu rekisteröidä torrent tracker';
$lang['BT_REG_FAIL_SAME_HASH'] = 'Toinen torrent kanssa samaa info_hash jo <a href="%s"><b>registered</b></a>';
$lang['BT_V1_ONLY_DISALLOWED'] = 'v1-only torrents have been disabled by the administrator at the moment, allowed: v2 and hybrids';
$lang['BT_V2_ONLY_DISALLOWED'] = 'v2-only torrents have been disabled by the administrator at the moment, allowed: v1 and hybrids';
$lang['BT_FLIST'] = 'Files list';
$lang['BT_FLIST_LIMIT'] = 'Tracker settings do not allow to process lists with more than %d files. Current number is: %d';
$lang['BT_FLIST_BTMR_HASH'] = 'BTMR Hash';
$lang['BT_FLIST_BTMR_NOTICE'] = 'BitTorrent Merkle Root is a hash of a file embedded in torrents with BitTorrent v2 support, tracker users can extract, calculate them, also download deduplicated torrents using desktop tools such as <a href="%s" target="_blank" referrerpolicy="origin">Torrent Merkle Root Reader</a>';
$lang['BT_FLIST_CREATION_DATE'] = 'Creation date';
$lang['BT_IS_PRIVATE'] = 'Private torrent';
$lang['BT_FLIST_FILE_PATH'] = 'Path (%s)';
$lang['BT_FLIST_LINK_TITLE'] = 'File hashes | .torrent meta-info';
$lang['BT_FLIST_ANNOUNCERS_LIST'] = 'Announcers list';
$lang['BT_FLIST_ANNOUNCERS'] = 'Announcers';
$lang['BT_FLIST_ANNOUNCERS_NOTICE'] = 'This list contains announcers of torrent file';
$lang['BT_UNREG_FROM_TRACKER'] = 'Poista tracker';
$lang['BT_UNREGISTERED'] = 'Torrent rekisteröimätön';
$lang['BT_UNREGISTERED_ALREADY'] = 'Torrent already unregistered';
$lang['BT_REGISTERED'] = 'Torrent rekisteröity tracker<br /><br />Now sinun täytyy <a href="%s"><b>download sinun torrent</b></a> ja ajaa se käyttämällä BitTorrent client valitsemalla kansion, jossa alkuperäiset tiedostot jaat, kun lataa polku';
$lang['INVALID_ANN_URL'] = 'Virheellinen Ilmoittaa URL [%s]<br /><br />must olla <b>%s</b>';
$lang['PASSKEY_ERR_TOR_NOT_REG'] = 'Ei voitu lisätä passkey<br /><br />Torrent ole rekisteröity tracker';
$lang['BT_PASSKEY'] = 'Salasana';
$lang['BT_GEN_PASSKEY'] = 'luo uusi';
$lang['BT_PASSKEY_VIEW'] = 'esityksessä';
$lang['BT_GEN_PASSKEY_NEW'] = "Huomio! Vaihdon jälkeen uusi salasana, sinun täytyy ladata uudelleen kaikki aktiiviset torrentit! \n oletko varma, että haluat luoda uuden salasanan?";
$lang['BT_NO_SEARCHABLE_FORUMS'] = 'Ei ole haettavissa foorumeilla löytyy';

$lang['SEEDS'] = 'Siemen';
$lang['LEECHS'] = 'Iilimato';
$lang['SPEED_UP'] = 'Nopeuttaa';
$lang['SPEED_DOWN'] = 'Nopeus Alas';

$lang['SEEDERS'] = 'Kylvökoneet';
$lang['LEECHERS'] = 'Leechers';
$lang['RELEASING'] = 'Itse';
$lang['SEEDING'] = 'Siemen';
$lang['LEECHING'] = 'Iilimato';
$lang['IS_REGISTERED'] = 'Rekisteröity';
$lang['MAGNET'] = 'Magnet-link';
$lang['MAGNET_FOR_GUESTS'] = 'Show magnet-link for guests';
$lang['MAGNET_v2'] = 'Magnet-link (BitTorrent v2 supported)';

//torrent status mod
$lang['TOR_STATUS'] = 'Tila';
$lang['TOR_STATUS_SELECT_ACTION'] = 'Valitse tila';
$lang['TOR_STATUS_NOT_SELECT'] = 'Et ole valinnut tilan.';
$lang['TOR_STATUS_SELECT_ALL'] = 'Kaikki statukset';
$lang['TOR_STATUS_FORBIDDEN'] = 'This topic\'s status is: ';
$lang['TOR_STATUS_NAME'] = [
    TOR_NOT_APPROVED => 'ei tarkastettu',
    TOR_CLOSED => 'suljettu',
    TOR_APPROVED => 'tarkastetaan',
    TOR_NEED_EDIT => 'ole virallistettu, kunnes',
    TOR_NO_DESC => 'ole virallistettu',
    TOR_DUP => 'duplicate',
    TOR_CLOSED_CPHOLD => 'closed (copyright)',
    TOR_CONSUMED => 'imeytyy',
    TOR_DOUBTFUL => 'kyseenalainen',
    TOR_CHECKING => 'being checked',
    TOR_TMP => 'väliaikainen',
    TOR_PREMOD => 'pre-maltillisesti',
    TOR_REPLENISH => 'replenishing',
];
$lang['TOR_STATUS_FAILED'] = 'Tällainen asema ei ole olemassa!';
$lang['TORRENT_FAILED'] = 'Jakelu ei löytynyt!';
$lang['TOR_STATUS_DUB'] = 'Jakelu on sama asema';
$lang['TOR_DONT_CHANGE'] = 'Change of status can not be performed!';
$lang['TOR_STATUS_OF'] = 'Jakelu on tilaa:';
$lang['TOR_STATUS_CHANGED'] = 'Tila muuttunut: ';
$lang['TOR_BACK'] = ' takaisin';
$lang['PROCEED'] = 'Jatka';
$lang['INVALID_ATTACH_ID'] = 'Puuttuva file identifier!';
$lang['CHANGE_TOR_TYPE'] = 'Kirjoita torrent onnistuneesti muuttunut';
$lang['DEL_TORRENT'] = 'Oletko varma, että haluat poistaa torrent?';
$lang['DEL_MOVE_TORRENT'] = 'Oletko varma, että haluat poistaa ja siirtää aihe?';
$lang['UNEXECUTED_RELEASE'] = 'Onko sinulla muodoton vapauttaa ennen kuin luot uuden korjata hänen muovaamattomien!';
$lang['TOR_STATUS_LOG_ACTION'] = 'New status: %s.<br/>Previous status: %s.';

// tor_comment
$lang['TOR_MOD_TITLE'] = 'Tilan muuttaminen jakelu - %s';
$lang['TOR_MOD_MSG'] = "Hei, %s.\n\n-Tilan [url=%s]your[/url] jakelu on muuttunut [b]%s[/b]";

$lang['TOR_AUTH_TITLE'] = 'Muutokset suunnittelu - %s';
$lang['TOR_AUTH_MSG'] = "Hei, %s.\n\n Tekee minun jakelu muuttunut - [url=%s]%s[/url]\n\n Ota uudelleen tarkistaa se.";
$lang['TOR_AUTH_FIXED'] = 'Kiinteä';
$lang['TOR_AUTH_SENT_COMMENT'] = ' &middot; <span class="seed bold">The tiedot lähetetään moderaattori. Odottaa.</span>';

$lang['BT_TOPIC_TITLE'] = 'Aiheen otsikko';
$lang['BT_SEEDER_LAST_SEEN'] = 'Seed viimeksi nähty';
$lang['BT_SORT_FORUM'] = 'Forum';
$lang['SIZE'] = 'Koko';
$lang['PIECE_LENGTH'] = 'Pala pituus';
$lang['COMPLETED'] = 'Completed downloads';
$lang['ADDED'] = 'Lisätty';
$lang['DELETE_TORRENT'] = 'Poista torrent';
$lang['DELETE_MOVE_TORRENT'] = 'Poistaa ja siirtää aihe';
$lang['DL_TORRENT'] = 'Lataa .torrent';
$lang['BT_LAST_POST'] = 'Viimeisin viesti';
$lang['BT_CREATED'] = 'Aihe lähetetty';
$lang['BT_REPLIES'] = 'Vastaukset';
$lang['BT_VIEWS'] = 'Näkymät';

// Gold/Silver releases
$lang['GOLD'] = 'Kultaa';
$lang['SILVER'] = 'Hopea';
$lang['SET_GOLD_TORRENT'] = 'Tehdä kultaa';
$lang['UNSET_GOLD_TORRENT'] = 'Tekemättömäksi kultaa';
$lang['SET_SILVER_TORRENT'] = 'Tee hopea';
$lang['UNSET_SILVER_TORRENT'] = 'Tekemättömäksi hopea';
$lang['GOLD_STATUS'] = 'KULTA TORRENT! LATAA LIIKENNE EI PIDÄ!';
$lang['SILVER_STATUS'] = 'HOPEA TORRENT! LATAA LIIKENNE OSITTAIN PITÄÄ!';
$lang['TOR_TYPE_LOG_ACTION'] = 'Torrent type changed to: %s';

$lang['TORRENT_STATUS'] = 'Search by status of release';
$lang['SEARCH_IN_FORUMS'] = 'Haku Foorumeilla';
$lang['SELECT_CAT'] = 'Valitse luokka';
$lang['GO_TO_SECTION'] = 'Goto-osiossa';
$lang['TORRENTS_FROM'] = 'Viestit';
$lang['SHOW_ONLY'] = 'Näytä vain';
$lang['SHOW_COLUMN'] = 'Näytä sarake';
$lang['SEL_CHAPTERS'] = 'Linkki valittu osioita';
$lang['NOT_SEL_CHAPTERS'] = 'Et ole valinnut aiheet';
$lang['SEL_CHAPTERS_HELP'] = 'Voit valita enintään %s osio';
$lang['HIDE_CONTENTS'] = 'Piilottaa sisällön {...}';
$lang['FILTER_BY_NAME'] = '<i>Filter nimen </i>';

$lang['BT_ONLY_ACTIVE'] = 'Aktiivinen';
$lang['BT_ONLY_MY'] = 'Minun tiedotteet';
$lang['BT_SEED_EXIST'] = 'Kylvää olemassa';
$lang['BT_ONLY_NEW'] = 'Uutta viime käyntisi';
$lang['BT_SHOW_CAT'] = 'Luokka';
$lang['BT_SHOW_FORUM'] = 'Forum';
$lang['BT_SHOW_AUTHOR'] = 'Kirjoittaja';
$lang['BT_SHOW_SPEED'] = 'Nopeus';
$lang['SEED_NOT_SEEN'] = 'Seeder ole nähnyt';
$lang['TITLE_MATCH'] = 'Otsikko ottelu';
$lang['BT_USER_NOT_FOUND'] = 'ei löytynyt';
$lang['DL_SPEED'] = 'Yleistä latausnopeus';

$lang['BT_DISREGARD'] = 'piittaamatta';
$lang['BT_NEVER'] = 'koskaan';
$lang['BT_ALL_DAYS_FOR'] = 'koko ajan';
$lang['BT_1_DAY_FOR'] = 'viimeinen päivä';
$lang['BT_3_DAY_FOR'] = 'viimeiset kolme päivää';
$lang['BT_7_DAYS_FOR'] = 'viime viikolla';
$lang['BT_2_WEEKS_FOR'] = 'viimeisen kahden viikon aikana';
$lang['BT_1_MONTH_FOR'] = 'viime kuussa';
$lang['BT_1_DAY'] = '1 päivä';
$lang['BT_3_DAYS'] = '3 päivää';
$lang['BT_7_DAYS'] = 'viikolla';
$lang['BT_2_WEEKS'] = '2 viikkoa';
$lang['BT_1_MONTH'] = 'kuukausi';

$lang['DL_LIST_AND_TORRENT_ACTIVITY'] = 'DL-Lista ja Torrent toimintaa';
$lang['DLWILL'] = 'Lataa';
$lang['DLDOWN'] = 'Lataaminen';
$lang['DLCOMPLETE'] = 'Täydellinen';
$lang['DLCANCEL'] = 'Peruuta';

$lang['DL_LIST_DEL'] = 'Selkeä DL-Lista';
$lang['DL_LIST_DEL_CONFIRM'] = 'Poista DL-Lista tästä aiheesta?';
$lang['SHOW_DL_LIST'] = 'Näytä DL-Lista';
$lang['SET_DL_STATUS'] = 'Lataa';
$lang['UNSET_DL_STATUS'] = 'Ei Lataa';
$lang['TOPICS_DOWN_SETS'] = 'Aihe muuttunut <b>Download</b>';
$lang['TOPICS_DOWN_UNSETS'] = '<b>Download</b> tila poistettu';

$lang['TOPIC_DL'] = 'DL';

$lang['MY_DOWNLOADS'] = 'Omat Lataukset';
$lang['SEARCH_DL_WILL'] = 'Suunnitteilla';
$lang['SEARCH_DL_WILL_DOWNLOADS'] = 'Suunnitteilla Lataukset';
$lang['SEARCH_DL_DOWN'] = 'Nykyinen';
$lang['SEARCH_DL_COMPLETE'] = 'Valmistunut';
$lang['SEARCH_DL_COMPLETE_DOWNLOADS'] = 'Päätökseen Lataukset';
$lang['SEARCH_DL_CANCEL'] = 'Peruttu';
$lang['CUR_DOWNLOADS'] = 'Nykyinen Lataukset';
$lang['CUR_UPLOADS'] = 'Nykyinen Lisäykset';
$lang['SEARCH_RELEASES'] = 'Tiedotteet';
$lang['TOR_SEARCH_TITLE'] = 'Torrent search vaihtoehtoja';
$lang['OPEN_TOPIC'] = 'Avoin aihe';

$lang['ALLOWED_ONLY_1ST_POST_ATTACH'] = 'Lähettämistä torrentit sallittu vain ensimmäinen viesti';
$lang['ALLOWED_ONLY_1ST_POST_REG'] = 'Rekisteröityminen torrentit sallittu vain ensimmäinen viesti';
$lang['REG_NOT_ALLOWED_IN_THIS_FORUM'] = 'Ei voitu rekisteröidä torrent tällä foorumilla';
$lang['ALREADY_REG'] = 'Torrent jo rekisteröity';
$lang['NOT_TORRENT'] = 'Tämä tiedosto ei ole torrent';
$lang['ONLY_1_TOR_PER_POST'] = 'Voit rekisteröidä vain yksi torrent yhden post';
$lang['ONLY_1_TOR_PER_TOPIC'] = 'Voit rekisteröidä vain yksi torrent yksi aihe';
$lang['VIEWING_USER_BT_PROFILE'] = 'Torrent-profile';
$lang['CUR_ACTIVE_DLS'] = 'Aktiivinen torrents';

$lang['TD_TRAF'] = 'Tänään';
$lang['YS_TRAF'] = 'Eilen';
$lang['TOTAL_TRAF'] = 'Yhteensä';

$lang['USER_RATIO'] = 'Suhde';
$lang['MAX_SPEED'] = 'Nopeus';
$lang['DOWNLOADED'] = 'Ladata';
$lang['UPLOADED'] = 'Ladataan';
$lang['RELEASED'] = 'Julkaistu';
$lang['BONUS'] = 'Harvoin';
$lang['IT_WILL_BE_DOWN'] = 'se alkaa olla pitää, kun se ladataan';
$lang['SPMODE_FULL'] = 'Näyttää ikäisensä täydelliset tiedot';

// Seed Bonus
$lang['MY_BONUS'] = 'Minun bonus (%s bonuksia varastossa)';
$lang['BONUS_SELECT'] = 'Valitse';
$lang['SEED_BONUS'] = 'Siemen bonus';
$lang['EXCHANGE'] = 'Vaihto';
$lang['EXCHANGE_BONUS'] = 'Vaihto siemen bonukset';
$lang['BONUS_UPLOAD_DESC'] = '<b>%s että distribution</b> <br /> vaihtaa pisteitä %1$s liikennettä, joka lisätään summa oman jakelun.';
$lang['BONUS_UPLOAD_PRICE'] = '<b class="%s">%s</b>';
$lang['PRICE'] = 'Hinta';
$lang['EXCHANGE_NOT'] = 'Vaihto ei ole saatavilla';
$lang['BONUS_SUCCES'] = 'Sinulle se on onnistuneesti värvätty %s';
$lang['BONUS_NOT_SUCCES'] = '<span class="leech">You ei ole bonuksia saatavilla. Enemmän kylvö!</span>';
$lang['BONUS_RETURN'] = 'Palaa siemen bonus exchange';

$lang['TRACKER'] = 'Tracker';
$lang['RANDOM_RELEASE'] = 'Random release';
$lang['OPEN_TOPICS'] = 'Avata aiheita';
$lang['OPEN_IN_SAME_WINDOW'] = 'avaa saman ikkunan';
$lang['SHOW_TIME_TOPICS'] = 'näyttää aika luoda aiheita';
$lang['SHOW_CURSOR'] = 'korosta rivi kursorin';

$lang['BT_LOW_RATIO_FOR_DL'] = "Suhde <b>%s</b> et voi ladata torrentit";
$lang['BT_RATIO_WARNING_MSG'] = 'Jos suhde alittaa %s, et voi ladata Torrentit! <a href="%s"><b>More luokitus.</b></a>';

$lang['SEEDER_LAST_SEEN'] = 'Seeder ole nähnyt: <b>%s</b>';

$lang['NEED_TO_LOGIN_FIRST'] = 'Sinun täytyy kirjautua ensin';
$lang['ONLY_FOR_MOD'] = 'Tämä vaihtoehto vain moderaattorit';
$lang['ONLY_FOR_ADMIN'] = 'Tämä vaihtoehto vain ylläpitäjät';
$lang['ONLY_FOR_SUPER_ADMIN'] = 'Tämä vaihtoehto vain super ylläpitäjät';

$lang['LOGS'] = 'Aihe historia';
$lang['FORUM_LOGS'] = 'Historia Forum';
$lang['AUTOCLEAN'] = 'Autoclean';
$lang['DESIGNER'] = 'Suunnittelija';

$lang['LAST_IP'] = 'Viimeinen IP:';
$lang['REG_IP'] = 'Rekisteröinti IP:';
$lang['OTHER_IP'] = 'Muita IP:';
$lang['ALREADY_REG_IP'] = 'IP-osoite on jo rekisteröity käyttäjä %s. Jos et ole aiemmin rekisteröity tracker, mail <a href="mailto:%s">Administrator</a>';
$lang['HIDDEN'] = 'Piilotettu';

// from admin
$lang['NOT_ADMIN'] = 'Et ole lupa hallinnoida tämän hallituksen';

$lang['COOKIES_REQUIRED'] = 'Evästeiden on oltava käytössä!';
$lang['SESSION_EXPIRED'] = 'Istunto vanhentunut';

// Sort memberlist per letter
$lang['POST_LINK'] = 'Post linkki';
$lang['GOTO_QUOTED_POST'] = 'Mene lainattu viesti';
$lang['LAST_VISITED'] = 'Viimeksi Käynyt';
$lang['LAST_ACTIVITY'] = 'Viimeinen toiminta';
$lang['NEVER'] = 'Koskaan';

//mpd
$lang['DELETE_POSTS'] = 'Poista valitut viestit';
$lang['DELETE_POSTS_SUCCESFULLY'] = 'Valitut viestit on poistettu onnistuneesti poistaa';
$lang['NO_POSTS_REMOVED'] = 'No posts were removed.';

//ts
$lang['TOPICS_ANNOUNCEMENT'] = 'Ilmoitukset';
$lang['TOPICS_STICKY'] = 'Stickies';
$lang['TOPICS_NORMAL'] = 'Aiheet';

//dpc
$lang['DOUBLE_POST_ERROR'] = 'Et voi tehdä toiseen virkaan täsmälleen sama teksti kuin viimeinen.';

//upt
$lang['UPDATE_POST_TIME'] = 'Päivitys postitse kerran';

$lang['TOPIC_SPLIT_NEW'] = 'Uusi aihe';
$lang['TOPIC_SPLIT_OLD'] = 'Vanha aihe';
$lang['BOT_LEAVE_MSG_MOVED'] = 'Lisää bot-viestin muuttoa';
$lang['BOT_REASON_MOVED'] = 'Reason to move';
$lang['BOT_AFTER_SPLIT_TO_OLD'] = 'Lisää bot-viestin split <b>old topic</b>';
$lang['BOT_AFTER_SPLIT_TO_NEW'] = 'Lisää bot-viestin split <b>new topic</b>';
//qr
$lang['QUICK_REPLY'] = 'Nopea Vastaus';
$lang['INS_NAME_TIP'] = 'Lisää nimi tai valitun tekstin.';
$lang['QUOTE_SELECTED'] = 'Lainaus valittu';
$lang['QR_ATTACHSIG'] = 'Kiinnitä allekirjoitus';
$lang['QR_NOTIFY'] = 'Ilmoita vastaus';
$lang['QR_DISABLE'] = 'Poistaa';
$lang['QR_USERNAME'] = 'Nimi';
$lang['NO_TEXT_SEL'] = 'Valitse tekstin tahansa sivu ja yritä uudelleen';
$lang['QR_FONT_SEL'] = 'Font face';
$lang['QR_COLOR_SEL'] = 'Fontin väri';
$lang['QR_SIZE_SEL'] = 'Fontin kokoa';
$lang['COLOR_STEEL_BLUE'] = 'Teräksen Sininen';
$lang['COLOR_GRAY'] = 'Harmaa';
$lang['COLOR_DARK_GREEN'] = 'Tumma Vihreä';

//txtb
$lang['ICQ_TXTB'] = '[ICQ]';
$lang['REPLY_WITH_QUOTE_TXTB'] = '[Quote]';
$lang['READ_PROFILE_TXTB'] = '[Profile]';
$lang['SEND_EMAIL_TXTB'] = '[E-mail]';
$lang['VISIT_WEBSITE_TXTB'] = '[www]';
$lang['EDIT_DELETE_POST_TXTB'] = '[Edit]';
$lang['CODE_TOPIC_TXTB'] = '[Code]';
$lang['SEARCH_USER_POSTS_TXTB'] = '[Search]';
$lang['VIEW_IP_TXTB'] = '[ip]';
$lang['DELETE_POST_TXTB'] = '[x]';
$lang['MODERATE_POST_TXTB'] = '[m]';
$lang['SEND_PM_TXTB'] = '[PM]';

$lang['DECLENSION']['REPLIES'] = ['vastaus', 'vastaukset'];
$lang['DECLENSION']['TIMES'] = ['aika', 'kertaa'];
$lang['DECLENSION']['FILES'] = ['file', 'files'];

$lang['DELTA_TIME']['INTERVALS'] = [
    'seconds' => ['toinen', 'sekuntia'],
    'minutes' => ['minuutin', 'minuuttia'],
    'hours' => ['tunti', 'tuntia'],
    'mday' => ['päivä', 'päivää'],
    'mon' => ['kuukausi', 'kuukautta'],
    'year' => ['vuonna', 'vuotta'],
];
$lang['DELTA_TIME']['FORMAT'] = '%1$s %2$s'; // 5(%1) minutes(%2)

$lang['AUTH_TYPES'][AUTH_ALL] = $lang['AUTH_ANONYMOUS_USERS'];
$lang['AUTH_TYPES'][AUTH_REG] = $lang['AUTH_REGISTERED_USERS'];
$lang['AUTH_TYPES'][AUTH_ACL] = $lang['AUTH_USERS_GRANTED_ACCESS'];
$lang['AUTH_TYPES'][AUTH_MOD] = $lang['AUTH_MODERATORS'];
$lang['AUTH_TYPES'][AUTH_ADMIN] = $lang['AUTH_ADMINISTRATORS'];

$lang['NEW_USER_REG_DISABLED'] = 'Anteeksi, rekisteröinti on käytössä tällä hetkellä';
$lang['ONLY_NEW_POSTS'] = 'vain uudet viestit';
$lang['ONLY_NEW_TOPICS'] = 'vain uusia aiheita';

$lang['TORHELP_TITLE'] = 'Auta kylvö nämä torrentit!';
$lang['STATISTICS'] = 'Tilastot';
$lang['STATISTIC'] = 'Tilasto';
$lang['VALUE'] = 'Arvo';
$lang['INVERT_SELECT'] = 'Käänteinen valinta';
$lang['STATUS'] = 'Tila';
$lang['LAST_CHANGED_BY'] = 'Viimeksi muutettu';
$lang['CHANGES'] = 'Muutokset';
$lang['ACTION'] = 'Toiminta';
$lang['REASON'] = 'Syy';
$lang['COMMENT'] = 'Kommentti';

// search
$lang['SEARCH_S'] = 'etsi...';
$lang['FORUM_S'] = 'foorumi';
$lang['TRACKER_S'] = 'tracker';
$lang['HASH_S'] = 'by info_hash';

// copyright
$lang['NOTICE'] = '!HUOMIO!';
$lang['COPY'] = 'Sivusto ei anna sähköisiä versioita tuotteita, ja on mukana vain kerätä ja luetteloinnin viittaukset lähetetään ja julkaistaan foorumin lukijoille. Jos olet laillinen omistaja tahansa toimittanut materiaalia, ja eivät halua, että viittaus oli valikoimaamme, ota meihin yhteyttä ja me heti poistaa hänen. Tiedostojen vaihto-tracker saavat käyttäjät, sivuston, ja hallinto ei ole vastuussa niiden ylläpidosta. Pyyntö ei täytä tiedostot suojattu tekijänoikeuksilla, ja myös tiedostoja laitonta huolto!';

// FILELIST
$lang['COLLAPSE'] = 'Romahdus hakemistoon';
$lang['EXPAND'] = 'Laajentaa';
$lang['SWITCH'] = 'Kytkin';
$lang['TOGGLE_WINDOW_FULL_SIZE'] = 'Increase/decrease the window';
$lang['EMPTY_ATTACH_ID'] = 'Puuttuva file identifier!';
$lang['TOR_NOT_FOUND'] = 'Tiedosto on puuttuu palvelimelle!';
$lang['ERROR_BUILD'] = 'Sisältö tämä torrent-tiedosto ei voida tarkastella sivuston (se ei ollut mahdollista rakentaa luettelo tiedostot)';
$lang['TORFILE_INVALID'] = 'Torrent-tiedosto on korruptoitunut';

// Profile
$lang['WEBSITE_ERROR'] = '"Sivusto" voi olla vain http://sitename';
$lang['ICQ_ERROR'] = 'Alan "ICQ" voi olla vain icq numero';
$lang['INVALID_DATE'] = 'Virhe päivämäärä ';
$lang['PROFILE_USER'] = 'Profiilin tarkasteleminen';
$lang['GOOD_UPDATE'] = 'oli onnistuneesti muuttunut';
$lang['UCP_DOWNLOADS'] = 'Lataukset';
$lang['HIDE_DOWNLOADS'] = 'Piilota nykyisen luettelon lataukset profiilissasi';
$lang['BAN_USER'] = 'Voit estää käyttäjän';
$lang['USER_NOT_ALLOWED'] = 'Käyttäjät eivät ole sallittuja';
$lang['HIDE_AVATARS'] = 'Näytä avatarit';
$lang['SHOW_CAPTION'] = 'Näyttää allekirjoitus';
$lang['DOWNLOAD_TORRENT'] = 'Lataa torrent';
$lang['SEND_PM'] = 'Lähetä PM';
$lang['SEND_MESSAGE'] = 'Lähetä viesti';
$lang['NEW_THREADS'] = 'Uusia Ketjuja';
$lang['PROFILE_NOT_FOUND'] = 'Profiilia ei löytynyt';

$lang['USER_DELETE'] = 'Poista';
$lang['USER_DELETE_EXPLAIN'] = 'Poista tämä käyttäjä';
$lang['USER_DELETE_ME'] = 'Anteeksi, tilisi ei saa poistaa!';
$lang['USER_DELETE_CSV'] = 'Anteeksi, tämä tili ei saa poistaa!';
$lang['USER_DELETE_CONFIRM'] = 'Oletko varma, että haluat poistaa tämän käyttäjän?';
$lang['USER_DELETED'] = 'Käyttäjä on poistettu onnistuneesti';
$lang['DELETE_USER_ALL_POSTS'] = 'Poistaa kaikki käyttäjän viestit';
$lang['DELETE_USER_ALL_POSTS_CONFIRM'] = 'Oletko varma, että haluat poistaa kaikki viestit ja kaikki aiheet alkoi tämän käyttäjän?';
$lang['DELETE_USER_POSTS'] = 'Poistaa kaikki viestit, paitsi pääomaa';
$lang['DELETE_USER_POSTS_ME'] = 'Oletko varma, että haluat poistaa kaikki minun virkaa?';
$lang['DELETE_USER_POSTS_CONFIRM'] = 'Oletko varma, että haluat poistaa kaikki viestit, paitsi pääomaa?';
$lang['USER_DELETED_POSTS'] = 'Viestit olivat onnistuneesti poistaa';

$lang['USER'] = 'Käyttäjä';
$lang['ROLE'] = 'Rooli:';
$lang['MEMBERSHIP_IN'] = 'Jäsenyys';
$lang['PARTY'] = 'Party:';
$lang['CANDIDATE'] = 'Ehdokas:';
$lang['INDIVIDUAL'] = 'On yksilön oikeuksia';
$lang['GROUP_LIST_HIDDEN'] = 'Sinulla ei ole oikeutta tarkastella piilotettuja ryhmiä';

$lang['USER_ACTIVATE'] = 'Aktivoi';
$lang['USER_DEACTIVATE'] = 'Poista käytöstä';
$lang['DEACTIVATE_CONFIRM'] = 'Oletko varma, että haluat ottaa käyttöön tämän käyttäjän?';
$lang['USER_ACTIVATE_ON'] = 'Käyttäjä on onnistuneesti aktivoitu';
$lang['USER_DEACTIVATE_ME'] = 'Et voi poistaa tilini!';
$lang['ACTIVATE_CONFIRM'] = 'Oletko varma, että haluat poistaa tämän käyttäjän?';
$lang['USER_ACTIVATE_OFF'] = 'Käyttäjä on onnistuneesti kytketty pois käytöstä';

// Register
$lang['CHOOSE_A_NAME'] = 'Kannattaa valita nimi';
$lang['CHOOSE_E_MAIL'] = 'Sinun täytyy määrittää e-mail';
$lang['CHOOSE_PASS'] = 'Kenttään salasana ei saa olla tyhjä!';
$lang['CHOOSE_PASS_ERR'] = 'Syötetyt salasanat eivät täsmää';
$lang['CHOOSE_PASS_ERR_MIN'] = 'Salasanan on oltava vähintään %d merkkiä';
$lang['CHOOSE_PASS_ERR_MAX'] = 'Salasana ei saa olla enää kuin $d merkkiä';
$lang['CHOOSE_PASS_ERR_NUM'] = 'The password must contain at least one digit';
$lang['CHOOSE_PASS_ERR_LETTER'] = 'The password must contain at least one letter of the Latin alphabet';
$lang['CHOOSE_PASS_ERR_LETTER_UPPERCASE'] = 'The password must contain at least one uppercase letter of the Latin alphabet';
$lang['CHOOSE_PASS_ERR_SPEC_SYMBOL'] = 'The password must contain at least one special character';
$lang['CHOOSE_PASS_OK'] = 'Salasanat täsmäävät';
$lang['CHOOSE_PASS_REG_OK'] = 'Salasanat täsmäävät, voit jatkaa rekisteröintiä';
$lang['CHOOSE_PASS_FAILED'] = 'Voit vaihtaa salasanan, sinun on oikein määrittää nykyinen salasana';
$lang['EMAILER_DISABLED'] = 'Anteeksi, tämä ominaisuus on tilapäisesti ei toimi';
$lang['TERMS_ON'] = 'Olen samaa mieltä näitä ehtoja';
$lang['TERMS_OFF'] = 'En ole samaa mieltä näitä ehtoja';
$lang['JAVASCRIPT_ON_REGISTER'] = 'Rekisteröityä, päät on tarpeen, jotta JavaScript';
$lang['REGISTERED_IN_TIME'] = "Tällä hetkellä rekisteröinti on closed<br /><br />You voi rekisteröidä 01:00 17:00 MSK (nyt " . date('H:i') . " MSK)<br /><br />We pahoittelemme tästä aiheutuvaa haittaa";
$lang['AUTOCOMPLETE'] = 'Salasana luo';
$lang['YOUR_NEW_PASSWORD'] = 'Olet uusi salasana:';
$lang['REGENERATE'] = 'Uudistua';

// Debug
$lang['EXECUTION_TIME'] = 'Suoritusaika:';
$lang['SEC'] = 'sec';
$lang['ON'] = 'päälle';
$lang['OFF'] = 'pois';
$lang['MEMORY'] = 'Mem: ';
$lang['QUERIES'] = 'kyselyt';
$lang['LIMIT'] = 'Raja:';
$lang['SHOW_LOG'] = 'Show log';
$lang['EXPLAINED_LOG'] = 'Explained log';
$lang['CUT_LOG'] = 'Cut long queries';

// Attach Guest
$lang['DOWNLOAD_INFO'] = 'Lataa ilmainen ja suurimmalla nopeudella!';
$lang['HOW_TO_DOWNLOAD'] = 'Miten Ladata?';
$lang['WHAT_IS_A_TORRENT'] = 'Mikä on torrent?';
$lang['RATINGS_AND_LIMITATIONS'] = 'Arvioinnista ja Rajoitukset';

$lang['SCREENSHOTS_RULES'] = 'Lue säännöt säädetyn kuvakaappauksia!';
$lang['SCREENSHOTS_RULES_TOPIC'] = 'Lue säännöt säädettyihin kuvakaappauksia tässä jaksossa!';
$lang['AJAX_EDIT_OPEN'] = 'Oletko jo avattu yksi nopea editointi!';
$lang['GO_TO_PAGE'] = 'Siirry sivulle ...';
$lang['EDIT'] = 'Muokkaa';
$lang['SAVE'] = 'Tallenna';
$lang['NEW_WINDOW'] = 'uudessa ikkunassa';

// BB Code
$lang['ALIGN'] = 'Kohdista:';
$lang['LEFT'] = 'Vasemmalle';
$lang['RIGHT'] = 'Oikealle';
$lang['CENTER'] = 'Keskitetty';
$lang['JUSTIFY'] = 'Sovita leveys';
$lang['HOR_LINE'] = 'Vaakasuora viiva (Ctrl+8)';
$lang['NEW_LINE'] = 'Uusi linja';
$lang['BOLD'] = 'Rohkea teksti: [b]text[/b] (Ctrl+B)';
$lang['ITALIC'] = 'Kursivoitu teksti: [i]text[/i] (Ctrl+I)';
$lang['UNDERLINE'] = 'Alleviivaa teksti: [u]text[/u] (Ctrl+U)';
$lang['STRIKEOUT'] = 'Yliviivattu teksti: [s]text[/s] (Ctrl+S)';
$lang['BOX_TAG'] = 'Frame around text: [box]text[/box] or [box=#333,#888]text[/box]';
$lang['INDENT_TAG'] = 'Insert indent: [indent]text[/indent]';
$lang['PRE_TAG'] = 'Preformatted text: [pre]text[/pre]';
$lang['NFO_TAG'] = 'NFO: [nfo]text[/nfo]';
$lang['SUPERSCRIPT'] = 'Superscript text: [sup]text[/sup]';
$lang['SUBSCRIPT'] = 'Subscript text: [sub]text[/sub]';
$lang['QUOTE_TITLE'] = 'Lainaus tekstistä: [quote]text[/quote] (Ctrl+Q)';
$lang['IMG_TITLE'] = 'Lisää kuva: [img]https://image_url[/img] (Ctrl+R)';
$lang['URL'] = 'Url';
$lang['URL_TITLE'] = 'Lisää URL-osoite: [url]https://url[/url] tai [url=https://url]URL-teksti[/url] (Ctrl+W)';
$lang['CODE_TITLE'] = 'Koodi näyttö: [code]code[/code] (Ctrl+K)';
$lang['LIST'] = 'Lista';
$lang['LIST_TITLE'] = 'Lista: [list]text[/list] (Ctrl+l)';
$lang['LIST_ITEM'] = 'Järjestetty lista: [list=]text[/list] (Ctrl+O)';
$lang['ACRONYM'] = 'Acronym';
$lang['ACRONYM_TITLE'] = 'Acronym: [acronym=Full text]Short text[/acronym]';
$lang['QUOTE_SEL'] = 'Lainaus valittu';
$lang['JAVASCRIPT_ON'] = 'Heads tarpeen lähettää viestejä JavaScript';

$lang['NEW'] = 'Uusi';
$lang['NEWEST'] = 'Uusin';
$lang['LATEST'] = 'Uusin';
$lang['POST'] = 'Post';
$lang['OLD'] = 'Vanha';

// DL-List
$lang['DL_USER'] = 'Käyttäjätunnus';
$lang['DL_PERCENT'] = 'Täydellinen prosenttia';
$lang['DL_UL'] = 'UL';
$lang['DL_DL'] = 'DL';
$lang['DL_UL_SPEED'] = 'UL-nopeus';
$lang['DL_DL_SPEED'] = 'DL-nopeus';
$lang['DL_PORT'] = 'Port';
$lang['DL_CLIENT'] = 'BitTorrent client';
$lang['DL_FORMULA'] = 'Kaava: Ladataan/TorrentSize';
$lang['DL_ULR'] = 'ULR';
$lang['DL_STOPPED'] = 'pysähtyi';
$lang['DL_UPD'] = 'upd: ';
$lang['DL_INFO'] = 'näyttää tiedot <i><b>only nykyisen session</b></i>';
$lang['HIDE_PEER_TORRENT_CLIENT'] = 'Hide my BitTorrent client name in peer list';
$lang['HIDE_PEER_COUNTRY_NAME'] = 'Hide my country name in peer list';
$lang['HIDE_PEER_USERNAME'] = 'Hide my username in peer list';

// Post PIN
$lang['POST_PIN'] = 'Pin-koodin ensimmäinen viesti';
$lang['POST_UNPIN'] = 'Unpin ensimmäinen viesti';
$lang['POST_PINNED'] = 'Ensimmäinen viesti puristuksiin';
$lang['POST_UNPINNED'] = 'Ensimmäinen viesti poistettu';

// Management of my messages
$lang['GOTO_MY_MESSAGE'] = 'Sulje ja palaa luetteloon "Omat Viestit"';
$lang['DEL_MY_MESSAGE'] = 'Valitut aiheet on poistettu "Omat Viestit"';
$lang['NO_TOPICS_MY_MESSAGE'] = 'Ei aiheita löytyy listan viestit (ehkä olet jo poistanut niitä)';
$lang['EDIT_MY_MESSAGE_LIST'] = 'muokkaa luetteloa';
$lang['SELECT_INVERT'] = 'select / invert';
$lang['RESTORE_ALL_POSTS'] = 'Palauttaa kaikki viestit';
$lang['DEL_LIST_MY_MESSAGE'] = 'Poista valittu aihe luettelosta';
$lang['DEL_LIST_MY_MESSAGE_INFO'] = 'Poistamisen jälkeen jopa päivittää <b>entire list</b> se voidaan osoittaa jo poistetut viestiketjut';
$lang['DEL_LIST_INFO'] = 'Voit poistaa tilauksen listasta, klikkaa kuvaketta vasemmassa nimet tahansa osassa';

// Watched topics
$lang['WATCHED_TOPICS'] = 'Katselin aiheita';
$lang['NO_WATCHED_TOPICS'] = 'No watching any topics';

// set_die_append_msg
$lang['INDEX_RETURN'] = 'Takaisin kotisivulle';
$lang['FORUM_RETURN'] = 'Takaisin forum';
$lang['TOPIC_RETURN'] = 'Takaisin aiheeseen';
$lang['POST_RETURN'] = 'Siirry viestiin';
$lang['PROFILE_EDIT_RETURN'] = 'Palaa editointi';
$lang['PROFILE_RETURN'] = 'Mene profiili';

$lang['WARNING'] = 'Varoitus';
$lang['INDEXER'] = 'Reindex haku';

$lang['FORUM_STYLE'] = 'Foorumin tyyli';

$lang['LINKS_ARE_FORBIDDEN'] = 'Linkit ovat kiellettyjä';

$lang['GENERAL'] = 'Yleinen Admin';
$lang['USERS'] = 'Käyttäjä Admin';
$lang['GROUPS'] = 'Ryhmän Admin';
$lang['FORUMS'] = 'Forum Admin';
$lang['MODS'] = 'Muutokset';

$lang['CONFIGURATION'] = 'Kokoonpano';
$lang['MANAGE'] = 'Hallinta';
$lang['DISALLOW'] = 'Estää nimet';
$lang['PRUNE'] = 'Karsinta';
$lang['MASS_EMAIL'] = 'Massa Email';
$lang['RANKS'] = 'Joukkoon';
$lang['SMILIES'] = 'Hymiöt';
$lang['BAN_MANAGEMENT'] = 'Kiellon Valvonta';
$lang['WORD_CENSOR'] = 'Sana Sensuroi';
$lang['EXPORT'] = 'Vienti';
$lang['CREATE_NEW'] = 'Luo';
$lang['ADD_NEW'] = 'Lisää';
$lang['CRON'] = 'Task Scheduler (cron)';
$lang['REBUILD_SEARCH_INDEX'] = 'Rakentaa haku indeksi';
$lang['FORUM_CONFIG'] = 'Foorumin asetuksia';
$lang['TRACKER_CONFIG'] = 'Tracker asetukset';
$lang['RELEASE_TEMPLATES'] = 'Julkaisu Malleja';
$lang['ACTIONS_LOG'] = 'Raportin toimintaa';

// Migrations
$lang['MIGRATIONS_STATUS']  = 'Database Migration Status';
$lang['MIGRATIONS_DATABASE_NAME']  = 'Database Name';
$lang['MIGRATIONS_DATABASE_TOTAL']  = 'Total Tables';
$lang['MIGRATIONS_DATABASE_SIZE']  = 'Database Size';
$lang['MIGRATIONS_DATABASE_INFO']  = 'Database Information';
$lang['MIGRATIONS_SYSTEM']  = 'Migration System';
$lang['MIGRATIONS_NEEDS_SETUP']  = 'Needs Setup';
$lang['MIGRATIONS_ACTIVE']  = 'Aktiivinen';
$lang['MIGRATIONS_NOT_INITIALIZED']  = 'Not Initialized';
$lang['MIGRATIONS_UP_TO_DATE']  = 'All up to date';
$lang['MIGRATIONS_PENDING_COUNT']  = 'pending';
$lang['MIGRATIONS_APPLIED']  = 'Applied Migrations';
$lang['MIGRATIONS_PENDING']  = 'Pending Migrations';
$lang['MIGRATIONS_VERSION']  = 'Version';
$lang['MIGRATIONS_NAME']  = 'Migration Name';
$lang['MIGRATIONS_FILE']  = 'Migration File';
$lang['MIGRATIONS_APPLIED_AT']  = 'Applied At';
$lang['MIGRATIONS_COMPLETED_AT']  = 'Completed At';
$lang['MIGRATIONS_CURRENT_VERSION']  = 'Current Version';
$lang['MIGRATIONS_NOT_APPLIED']  = 'No migrations applied';
$lang['MIGRATIONS_INSTRUCTIONS']  = 'Instructions';
$lang['MIGRATIONS_SETUP_STATUS']  = 'Setup Status';
$lang['MIGRATIONS_SETUP_GUIDE']  = 'See setup guide below';
$lang['MIGRATIONS_ACTION_REQUIRED']  = 'Action Required';

// Index
$lang['MAIN_INDEX'] = 'Forum-Index';
$lang['FORUM_STATS'] = 'Foorumin Tilastot';
$lang['ADMIN_INDEX'] = 'Admin Indeksi';
$lang['CREATE_PROFILE'] = 'Luo profiili';

$lang['TP_VERSION'] = 'TorrentPier versio';
$lang['TP_RELEASE_DATE'] = 'Julkaisupäivä';
$lang['PHP_INFO'] = 'Tietoa PHP';

$lang['CLICK_RETURN_ADMIN_INDEX'] = 'Klikkaa %sHere%s palata Admin Indeksi';

$lang['NUMBER_POSTS'] = 'Virkojen määrä';
$lang['POSTS_PER_DAY'] = 'Viestejä per päivä';
$lang['NUMBER_TOPICS'] = 'Useita aiheita';
$lang['TOPICS_PER_DAY'] = 'Aiheita päivässä';
$lang['NUMBER_USERS'] = 'Käyttäjien määrä';
$lang['USERS_PER_DAY'] = 'Käyttäjiä per päivä';
$lang['BOARD_STARTED'] = 'Hallitus alkoi';
$lang['AVATAR_DIR_SIZE'] = 'Avatar hakemistoon kokoa';
$lang['DATABASE_SIZE'] = 'Tietokannan koko';
$lang['GZIP_COMPRESSION'] = 'Gzip compression';
$lang['NOT_AVAILABLE'] = 'Ei saatavilla';

// System information
$lang['ADMIN_SYSTEM_INFORMATION'] = 'System information';
$lang['ADMIN_SYSTEM_OS'] = 'OS:';
$lang['ADMIN_SYSTEM_PHP_VER'] = 'PHP:';
$lang['ADMIN_SYSTEM_DATABASE_VER'] = 'Database:';
$lang['ADMIN_SYSTEM_PHP_MEM_LIMIT'] = 'Memory limit:';
$lang['ADMIN_SYSTEM_DISK_SPACE_INFO_TITLE'] = 'Disk space info:';
$lang['ADMIN_SYSTEM_DISK_SPACE_INFO'] = '%s (used: %s | free: %s)';
$lang['ADMIN_SYSTEM_PHP_MAX_EXECUTION_TIME'] = 'Max execution time:';

// Clear Cache
$lang['DATASTORE'] = 'Datastore';
$lang['CLEAR_CACHE'] = 'Cache';
$lang['CLEAR_TEMPLATES_CACHE'] = 'Templates cache';

// Update
$lang['USER_LEVELS'] = 'Käyttäjän tasolla';
$lang['USER_LEVELS_UPDATED'] = 'Käyttäjän tasot on päivitetty';

// Synchronize
$lang['SYNCHRONIZE'] = 'Synkronoida';
$lang['TOPICS_DATA_SYNCHRONIZED'] = 'Aiheet, tiedot on synkronoitu';
$lang['USER_POSTS_COUNT'] = 'Käyttäjän viestit count';
$lang['USER_POSTS_COUNT_SYNCHRONIZED'] = 'Käyttäjän viestit määrä on ollut synkronoitu';

// Online Userlist
$lang['SHOW_ONLINE_USERLIST'] = 'Näyttää luettelon online käyttäjiä';

// Robots.txt editor
$lang['ROBOTS_TXT_EDITOR_TITLE'] = 'Manage robots.txt';
$lang['ROBOTS_TXT_UPDATED_SUCCESSFULLY'] = 'File robots.txt has been updated successfully';
$lang['CLICK_RETURN_ROBOTS_TXT_CONFIG'] = '%sClick Here to return to robots.txt manager%s';

// Auth pages
$lang['USER_SELECT'] = 'Valitse Käyttäjä';
$lang['GROUP_SELECT'] = 'Valitse Ryhmä';
$lang['SELECT_A_FORUM'] = 'Valitse foorumi';
$lang['AUTH_CONTROL_USER'] = 'Käyttöoikeudet Ohjaus';
$lang['AUTH_CONTROL_GROUP'] = 'Ryhmän Käyttöoikeudet Ohjaus';
$lang['AUTH_CONTROL_FORUM'] = 'Forum Permissions Control';
$lang['LOOK_UP_FORUM'] = 'Katso ylös Forum';

$lang['GROUP_AUTH_EXPLAIN'] = 'Täällä voit muuttaa käyttöoikeuksia ja moderaattori tilan kullekin käyttäjäryhmälle. Älä unohda, kun muutat ryhmän käyttöoikeudet, että yksittäisen käyttäjän käyttöoikeuksia voi silti antaa käyttäjälle pääsyn foorumeilla, jne. Saat varoituksen, jos tämä on tapauksessa.';
$lang['USER_AUTH_EXPLAIN'] = 'Täällä voit muuttaa käyttöoikeuksia ja moderaattori tilan kullekin yksittäiselle käyttäjälle. Älä unohda, kun muuttaa käyttäjien oikeuksia, että ryhmän käyttöoikeudet, voivat edelleen sallia käyttäjälle pääsyn foorumeilla, jne. Saat varoituksen, jos tämä on tapauksessa.';
$lang['FORUM_AUTH_EXPLAIN'] = 'Täällä voit muuttaa luvan tasot kunkin foorumi. Sinulla on sekä yksinkertainen ja kehittynyt menetelmä, jossa advanced tarjoaa enemmän valvontaa kunkin foorumin toimintaa. Muista, että muuttamalla lupaa tason foorumeilla vaikuttaa, jotka käyttäjät voivat suorittaa erilaisia toimintoja niiden sisällä.';

$lang['SIMPLE_MODE'] = 'Yksinkertainen Tila';
$lang['ADVANCED_MODE'] = 'Advanced-Tilassa';
$lang['MODERATOR_STATUS'] = 'Moderaattori-status';

$lang['ALLOWED_ACCESS'] = 'Pääsy Sallittu';
$lang['DISALLOWED_ACCESS'] = 'Luvaton Pääsy';
$lang['IS_MODERATOR'] = 'On Moderaattori';

$lang['CONFLICT_WARNING'] = 'Lupa Konflikti Varoitus';
$lang['CONFLICT_ACCESS_USERAUTH'] = 'Tämä käyttäjä on edelleen käyttöoikeus tämän foorumin kautta ryhmän jäsenyys. Haluat ehkä muuttaa ryhmän käyttöoikeudet tai poistaa tämän käyttäjän ryhmä, täysin estää niitä, joilla on käyttöoikeudet. Ryhmien oikeuksien myöntäminen (ja foorumeilla mukana) ovat huomautti alla.';
$lang['CONFLICT_MOD_USERAUTH'] = 'Tämä käyttäjä on edelleen moderaattorin oikeudet tämän foorumin kautta ryhmän jäsenyys. Haluat ehkä muuttaa ryhmän käyttöoikeudet tai poistaa tämän käyttäjän ryhmä, täysin estää niitä, joilla on valvojan oikeudet. Ryhmien oikeuksien myöntäminen (ja foorumeilla mukana) ovat huomautti alla.';

$lang['CONFLICT_ACCESS_GROUPAUTH'] = 'Seuraava käyttäjä (tai käyttäjät) on vielä käyttöoikeudet tämän foorumin kautta käyttäjän lupaa asetuksia. Haluat ehkä muuttaa käyttöoikeuksia täysin estää niitä, joilla on käyttöoikeudet. Käyttäjien oikeuksia (ja foorumeilla mukana) ovat huomautti alla.';
$lang['CONFLICT_MOD_GROUPAUTH'] = 'Seuraava käyttäjä (tai käyttäjät) on vielä moderaattori oikeudet tämän foorumin kautta niiden käyttöoikeudet asetukset. Haluat ehkä muuttaa käyttöoikeuksia täysin estää ne ottaa moderaattori-oikeudet. Käyttäjien oikeuksia (ja foorumeilla mukana) ovat huomautti alla.';

$lang['PUBLIC'] = 'Julkinen';
$lang['PRIVATE'] = 'Yksityinen';
$lang['REGISTERED'] = 'Rekisteröity';
$lang['ADMINISTRATORS'] = 'Ylläpitäjät';

// These are displayed in the drop-down boxes for advanced mode forum auth, try and keep them short!
$lang['FORUM_ALL'] = 'KAIKKI';
$lang['FORUM_REG'] = 'REG';
$lang['FORUM_PRIVATE'] = 'YKSITYINEN';
$lang['FORUM_MOD'] = 'MOD';
$lang['FORUM_ADMIN'] = 'ADMIN';

$lang['AUTH_VIEW'] = $lang['VIEW'] = 'Näkymä';
$lang['AUTH_READ'] = $lang['READ'] = 'Lue';
$lang['AUTH_POST'] = $lang['POST'] = 'Post';
$lang['AUTH_REPLY'] = $lang['REPLY'] = 'Vastaus';
$lang['AUTH_EDIT'] = $lang['EDIT'] = 'Muokkaa';
$lang['AUTH_DELETE'] = $lang['DELETE'] = 'Poista';
$lang['AUTH_STICKY'] = $lang['STICKY'] = 'Tahmea';
$lang['AUTH_ANNOUNCE'] = $lang['ANNOUNCE'] = 'Ilmoittaa';
$lang['AUTH_VOTE'] = $lang['VOTE'] = 'Äänestys';
$lang['AUTH_POLLCREATE'] = $lang['POLLCREATE'] = 'Kyselyn luominen';
$lang['AUTH_ATTACHMENTS'] = $lang['AUTH_ATTACH'] = 'Post Tiedostot';
$lang['AUTH_DOWNLOAD'] = 'Lataa Tiedostot';

$lang['SIMPLE_PERMISSION'] = 'Yksinkertainen Käyttöoikeudet';

$lang['USER_LEVEL'] = 'Käyttäjän Tasolla';
$lang['AUTH_USER'] = 'Käyttäjä';
$lang['AUTH_ADMIN'] = 'Ylläpitäjä';
$lang['GROUP_MEMBERSHIPS'] = 'Käyttäjäryhmän jäsenyydet';
$lang['USERGROUP_MEMBERS'] = 'Tämä ryhmä on seuraavat jäsenet';

$lang['FORUM_AUTH_UPDATED'] = 'Forum käyttöoikeudet on päivitetty';
$lang['USER_AUTH_UPDATED'] = 'Käyttöoikeudet on päivitetty';
$lang['GROUP_AUTH_UPDATED'] = 'Ryhmän käyttöoikeudet on päivitetty';

$lang['AUTH_UPDATED'] = 'Käyttöoikeudet on päivitetty';
$lang['AUTH_GENERAL_ERROR'] = 'Could not update admin status';
$lang['AUTH_SELF_ERROR'] = 'Could not change yourself from an admin to user';
$lang['CLICK_RETURN_USERAUTH'] = 'Klikkaa %sHere%s palata Käyttöoikeudet';
$lang['CLICK_RETURN_GROUPAUTH'] = 'Klikkaa %sHere%s palata Ryhmän Käyttöoikeudet';
$lang['CLICK_RETURN_FORUMAUTH'] = 'Klikkaa %sHere%s palata Foorumin Oikeudet';

// Banning
$lang['BAN_CONTROL'] = 'Kiellon Valvonta';
$lang['BAN_EXPLAIN'] = 'Here you can control the banning of users.';
$lang['BAN_USERNAME'] = 'Ban yksi tai useampi tietyille käyttäjille';
$lang['BAN_USERNAME_EXPLAIN'] = 'Voit estää useita käyttäjiä kerralla käyttämällä sopivan yhdistelmän hiirtä ja näppäimistöä tietokoneen ja selaimen';
$lang['UNBAN_USERNAME'] = 'Unban one more specific users';
$lang['UNBAN_USERNAME_EXPLAIN'] = 'Voit unban useille käyttäjille kerralla käyttämällä sopivan yhdistelmän hiirtä ja näppäimistöä tietokoneen ja selaimen';
$lang['NO_BANNED_USERS'] = 'Ei ole kielletty käyttäjätunnuksia';
$lang['BAN_UPDATE_SUCESSFUL'] = 'Se bannilista on päivitetty onnistuneesti';
$lang['CLICK_RETURN_BANADMIN'] = 'Klikkaa %sHere%s palata Kiellon Valvonta';

// Configuration
$lang['GENERAL_CONFIG'] = 'Yleinen Kokoonpano';
$lang['CONFIG_EXPLAIN'] = 'Alla olevan lomakkeen avulla voit muokata kaikkia hallintoneuvoston valinnat. Käyttäjän ja Forum kokoonpanoissa käytä linkkejä vasemmalla puolella.';

$lang['CONFIG_MODS'] = 'Kokoonpano muutoksia';
$lang['MODS_EXPLAIN'] = 'Tämän lomakkeen avulla voit säätää muutoksia';

$lang['CLICK_RETURN_CONFIG'] = '%sClick Täällä palaa Yleensä Configuration%s';
$lang['CLICK_RETURN_CONFIG_MODS'] = '%sBack asetukset modifications%s';

$lang['GENERAL_SETTINGS'] = 'Yleensä Hallituksen Asetukset';
$lang['SITE_NAME'] = 'Sivuston nimi';
$lang['SITE_DESC'] = 'Sivuston kuvaus';
$lang['FORUMS_DISABLE'] = 'Poistaa aluksella';
$lang['BOARD_DISABLE_EXPLAIN'] = 'Tämä tekee hallituksen käytettävissä käyttäjille. Järjestelmänvalvojat voivat käyttää Hallinnon Paneeli, kun hallitus on poistettu käytöstä.';
$lang['ACCT_ACTIVATION'] = 'Jotta tilin aktivointi';
$lang['ACC_NONE'] = 'Ei mitään'; // These three entries are the type of activation
$lang['ACC_USER'] = 'Käyttäjä';
$lang['ACC_ADMIN'] = 'Admin';

$lang['ABILITIES_SETTINGS'] = 'Käyttäjä ja Foorumin Perus Asetukset';
$lang['MAX_POLL_OPTIONS'] = 'Max määrä kyselyn vaihtoehtoja';
$lang['FLOOD_INTERVAL'] = 'Tulva Välein';
$lang['FLOOD_INTERVAL_EXPLAIN'] = 'Määrä sekuntia käyttäjä saa odottaa välillä virkaa';
$lang['TOPICS_PER_PAGE'] = 'Aiheita Per Sivu';
$lang['POSTS_PER_PAGE'] = 'Viestejä Per Sivu';
$lang['HOT_THRESHOLD'] = 'Virkaa Suosittu Kynnys';
$lang['DEFAULT_LANGUAGE'] = 'Oletuskieli';
$lang['DATE_FORMAT'] = 'Päivämäärän Muoto';
$lang['SYSTEM_TIMEZONE'] = 'Järjestelmän Aikavyöhyke';
$lang['ENABLE_PRUNE'] = 'Jotta Foorumin Karsimisesta';
$lang['ALLOW_BBCODE'] = 'Salli BBCode';
$lang['ALLOW_SMILIES'] = 'Salli Hymiöt';
$lang['SMILIES_PATH'] = 'Hymiöt Varastointi Polku';
$lang['SMILIES_PATH_EXPLAIN'] = 'Polku alla TorrentPier root dir, esim. tyylit/images/smiles';
$lang['ALLOW_SIG'] = 'Salli Allekirjoitukset';
$lang['MAX_SIG_LENGTH'] = 'Suurin allekirjoitus pituus';
$lang['MAX_SIG_LENGTH_EXPLAIN'] = 'Merkkien enimmäismäärä käyttäjän allekirjoitukset';
$lang['ALLOW_NAME_CHANGE'] = 'Anna Käyttäjätunnus muutoksia';

$lang['EMAIL_SETTINGS'] = 'Sähköposti Asetukset';

// Visual Confirmation
$lang['VISUAL_CONFIRM'] = 'Avulla Visuaalinen Vahvistus';
$lang['VISUAL_CONFIRM_EXPLAIN'] = 'Vaatii käyttäjiä syötä koodi on määritelty kuvan, kun rekisteröitymättä.';

// Autologin Keys
$lang['ALLOW_AUTOLOGIN'] = 'Salli automaattiset kirjautumiset';
$lang['ALLOW_AUTOLOGIN_EXPLAIN'] = 'Määrittää, onko käyttäjillä on mahdollisuus valita automaattisesti kirjautuneena kun vierailet foorumilla';
$lang['AUTOLOGIN_TIME'] = 'Automaattinen kirjautuminen avain päättymistä';
$lang['AUTOLOGIN_TIME_EXPLAIN'] = 'Kuinka kauan on autologin avain on voimassa päivää, jos käyttäjä ei käy hallitukselle. Nolla poistaa päättymistä.';

// Forum Management
$lang['FORUM_ADMIN_MAIN'] = 'Foorumin Hallinto';
$lang['FORUM_ADMIN_EXPLAIN'] = 'Tässä paneelissa voit lisätä, poistaa, muokata, järjestää uudelleen ja uudelleen synkronoi kategoriat ja foorumit';
$lang['EDIT_FORUM'] = 'Edit forum';
$lang['CREATE_FORUM'] = 'Luo uusi foorumi';
$lang['CREATE_SUB_FORUM'] = 'Create subforum';
$lang['CREATE_CATEGORY'] = 'Luo uusi luokka';
$lang['REMOVE'] = 'Poista';
$lang['UPDATE_ORDER'] = 'Päivitys Jotta';
$lang['CONFIG_UPDATED'] = 'Foorumin Kokoonpano On Päivitetty Onnistuneesti';
$lang['MOVE_UP'] = 'Siirrä ylös';
$lang['MOVE_DOWN'] = 'Siirrä alas';
$lang['RESYNC'] = 'Resync';
$lang['NO_MODE'] = 'Ei tilassa oli asetettu';
$lang['FORUM_EDIT_DELETE_EXPLAIN'] = 'Alla olevan lomakkeen avulla voit muokata kaikkia hallintoneuvoston valinnat. Käyttäjän ja Forum kokoonpanoissa käytä linkkejä vasemmalla puolella';

$lang['MOVE_CONTENTS'] = 'Siirtää kaikki sisältö';
$lang['FORUM_DELETE'] = 'Poista Forum';
$lang['FORUM_DELETE_EXPLAIN'] = 'Alla olevan lomakkeen avulla voit poistaa foorumi (tai luokan) ja päättää, missä haluat laittaa kaikki aiheet (tai foorumit) se sisälsi.';
$lang['CATEGORY_DELETE'] = 'Poista Luokka';
$lang['CATEGORY_NAME_EMPTY'] = 'Category name not specified';

$lang['STATUS_LOCKED'] = 'Lukittu';
$lang['STATUS_UNLOCKED'] = 'Auki';
$lang['FORUM_SETTINGS'] = 'Yleiset Foorumin Asetuksia';
$lang['FORUM_NAME'] = 'Foorumin nimi';
$lang['FORUM_DESC'] = 'Kuvaus';
$lang['FORUM_STATUS'] = 'Foorumin tila';
$lang['FORUM_PRUNING'] = 'Auto-karsinta';

$lang['PRUNE_DAYS'] = 'Poistaa aiheita, joita ei ole kirjattu vuonna';
$lang['SET_PRUNE_DATA'] = 'Sinulla on käytössä auto-luumu tällä foorumilla, mutta ei ole asetettu useita päiviä karsia. Mene takaisin ja tehdä niin.';

$lang['MOVE_AND_DELETE'] = 'Siirrä ja Poista';

$lang['DELETE_ALL_POSTS'] = 'Poistaa kaikki viestit';
$lang['DELETE_ALL_TOPICS'] = 'Poistaa kaikki aiheet, mukaan lukien ilmoitukset ja tahmea';
$lang['NOWHERE_TO_MOVE'] = 'Missään siirtyä';

$lang['EDIT_CATEGORY'] = 'Muokkaa Luokka';
$lang['EDIT_CATEGORY_EXPLAIN'] = 'Tämän lomakkeen avulla voit muuttaa luokan nimeä.';

$lang['FORUMS_UPDATED'] = 'Foorumi ja Luokan tiedot päivitetty onnistuneesti';

$lang['MUST_DELETE_FORUMS'] = 'Sinun täytyy poistaa kaikki foorumit ennen kuin voit poistaa tämän kategorian?';

$lang['CLICK_RETURN_FORUMADMIN'] = 'Klikkaa %sHere%s palata Foorumin Hallinto';

$lang['SHOW_ALL_FORUMS_ON_ONE_PAGE'] = 'Näytä kaikki foorumit yhdellä sivulla';

// Smiley Management
$lang['SMILEY_TITLE'] = 'Hymyilee Editointi Apuohjelma';
$lang['SMILE_DESC'] = 'Tällä sivulla voit lisätä, poistaa ja muokata hymiöitä tai hymiöt, että käyttäjät voivat käyttää niiden viestit ja yksityiset viestit.';

$lang['SMILEY_CONFIG'] = 'Hymiö Kokoonpano';
$lang['SMILEY_CODE'] = 'Hymiö Koodi';
$lang['SMILEY_URL'] = 'Hymiö Kuva Tiedosto';
$lang['SMILEY_EMOT'] = 'Hymiö Tunteita';
$lang['SMILE_ADD'] = 'Lisää uusi Hymiö';
$lang['SMILE'] = 'Hymy';
$lang['EMOTION'] = 'Tunteet';

$lang['SELECT_PAK'] = 'Valitse Pack (.pak) - Tiedoston';
$lang['REPLACE_EXISTING'] = 'Korvata Nykyiset Hymiö';
$lang['KEEP_EXISTING'] = 'Säilytä Olemassa Oleva Hymiö';
$lang['SMILEY_IMPORT_INST'] = 'Sinun pitäisi purkaa hymiö paketti ja ladata kaikki tiedostot sopiva Hymiö hakemiston asennus. Valitse sitten oikea tieto tässä muodossa tuo hymiö pack.';
$lang['SMILEY_IMPORT'] = 'Smiley Pack Tuonti';
$lang['CHOOSE_SMILE_PAK'] = 'Valitse Hymy Pack .pak -';
$lang['IMPORT'] = 'Tuonti Hymiöt';
$lang['SMILE_CONFLICTS'] = 'Mitä pitäisi tehdä siinä tapauksessa, että konfliktit';
$lang['DEL_EXISTING_SMILEYS'] = 'Poista olemassa olevat hymiöt ennen tuontia';
$lang['IMPORT_SMILE_PACK'] = 'Tuo Hymiö Pack';
$lang['EXPORT_SMILE_PACK'] = 'Luoda Smiley Pack';
$lang['EXPORT_SMILES'] = 'Voit luoda smiley pack tällä hetkellä asennettu hymiöt, valitse %sHere%s ladata hymyilee.pak-tiedoston avaamiseen. Nimi tämän tiedoston asianmukaisesti varmista pitää .pak-tiedostotunnistetta. Sitten luoda zip-tiedoston, joka sisältää kaikki hymiö kuvia plus tämä .pak configuration file.';

$lang['SMILEY_ADD_SUCCESS'] = 'Hymiö on lisätty onnistuneesti';
$lang['SMILEY_EDIT_SUCCESS'] = 'Hymiö on päivitetty onnistuneesti';
$lang['SMILEY_IMPORT_SUCCESS'] = 'Hymiö Pack on tuotu onnistuneesti!';
$lang['SMILEY_DEL_SUCCESS'] = 'Hymiö on poistettu onnistuneesti';
$lang['CLICK_RETURN_SMILEADMIN'] = 'Klikkaa %sHere%s palata Hymiö Hallinto';

// User Management
$lang['USER_ADMIN'] = 'Käyttäjä Hallinto';
$lang['USER_ADMIN_EXPLAIN'] = 'Täällä voit muuttaa käyttäjien tietoja ja tiettyjä vaihtoehtoja. Voit muokata käyttäjien käyttöoikeuksia, käytä käyttäjän ja ryhmän oikeudet järjestelmään.';

$lang['LOOK_UP_USER'] = 'Katso ylös käyttäjä';

$lang['ADMIN_USER_FAIL'] = 'Ei voinut päivittää käyttäjän profiiliin.';
$lang['ADMIN_USER_UPDATED'] = 'Käyttäjän profiili on päivitetty onnistuneesti.';
$lang['CLICK_RETURN_USERADMIN'] = 'Klikkaa %sHere%s palata User Administration';

$lang['USER_ALLOWPM'] = 'Voi lähettää Yksityisiä Viestejä';
$lang['USER_ALLOWAVATAR'] = 'Voi näyttää avatar';

$lang['ADMIN_AVATAR_EXPLAIN'] = 'Täällä voit nähdä ja poistaa nykyisen käyttäjän avatar.';

$lang['USER_SPECIAL'] = 'Erityistä admin-vain kentät';
$lang['USER_SPECIAL_EXPLAIN'] = 'Nämä kentät eivät voi olla muutettu käyttäjille. Täällä voit asettaa niiden tilan, ja muita vaihtoehtoja, jotka eivät ole antaneet käyttäjille.';

// Group Management
$lang['GROUP_ADMINISTRATION'] = 'Konsernin Hallinto';
$lang['GROUP_ADMIN_EXPLAIN'] = 'Tässä paneelissa voit hallinnoida kaikkia käyttäjäryhmiä. Voit poistaa, luoda ja muokata olemassa olevia ryhmiä. Voit valita moderaattorit, toggle avoin/suljettu ryhmä, tilan ja asettaa ryhmän nimi ja kuvaus';
$lang['ERROR_UPDATING_GROUPS'] = 'Siellä oli virhe, kun olet päivittänyt ryhmät';
$lang['UPDATED_GROUP'] = 'Ryhmä on päivitetty onnistuneesti';
$lang['ADDED_NEW_GROUP'] = 'Uusi ryhmä on luotu onnistuneesti';
$lang['DELETED_GROUP'] = 'Ryhmä on poistettu onnistuneesti';
$lang['CREATE_NEW_GROUP'] = 'Luo uusi ryhmä';
$lang['EDIT_GROUP'] = 'Muokkaa ryhmää';
$lang['GROUP_STATUS'] = 'Ryhmän tila';
$lang['GROUP_DELETE'] = 'Poista ryhmä';
$lang['GROUP_DELETE_CHECK'] = 'Poista tämä ryhmä';
$lang['SUBMIT_GROUP_CHANGES'] = 'Lähetä Muutokset';
$lang['RESET_GROUP_CHANGES'] = 'Reset Muutokset';
$lang['NO_GROUP_NAME'] = 'Sinun on määritettävä nimi tälle ryhmälle';
$lang['NO_GROUP_MODERATOR'] = 'Sinun täytyy määrittää moderaattori tämän ryhmän';
$lang['NO_GROUP_MODE'] = 'Sinun täytyy määrittää-tilassa tämä ryhmä, avoin tai suljettu';
$lang['NO_GROUP_ACTION'] = 'Mitään toimia ei ole määritelty';
$lang['DELETE_OLD_GROUP_MOD'] = 'Poista vanha ryhmän moderaattori?';
$lang['DELETE_OLD_GROUP_MOD_EXPL'] = 'Jos olet muuttamassa ryhmän moderaattori, valitse tämä valintaruutu, jos haluat poistaa vanha moderaattori ryhmästä. Muuten, älä tarkista se, ja käyttäjän tulee säännöllisesti ryhmän jäsen.';
$lang['CLICK_RETURN_GROUPSADMIN'] = 'Klikkaa %sHere%s palata Ryhmän Hallinto.';
$lang['SELECT_GROUP'] = 'Valitse ryhmä';
$lang['LOOK_UP_GROUP'] = 'Katso ylös ryhmä';

// Prune Administration
$lang['FORUM_PRUNE'] = 'Forum Karsia';
$lang['FORUM_PRUNE_EXPLAIN'] = 'Tämä poistaa minkä tahansa aiheen, jota ei ole lähetetty sisällä monta päivää valitset. Jos et syötä numero, sitten kaikki aiheet poistetaan. Se ei poista <b>sticky</b> aiheita ja <b>announcements</b>. Sinun täytyy poistaa ne aiheet manuaalisesti.';
$lang['DO_PRUNE'] = 'Älä Karsia';
$lang['ALL_FORUMS'] = 'Kaikki Foorumit';
$lang['PRUNE_TOPICS_NOT_POSTED'] = 'Karsia aiheita, joilla ei ole vastauksia tässä monta päivää';
$lang['TOPICS_PRUNED'] = 'Aiheita karsittiin';
$lang['POSTS_PRUNED'] = 'Viestit karsitaan';
$lang['PRUNE_SUCCESS'] = 'Foorumi on ollut karsittiin onnistuneesti';
$lang['NOT_DAYS'] = 'Karsia päivää ole valittu';

// Word censor
$lang['WORDS_TITLE'] = 'Sana Sensuroidaan';
$lang['WORDS_EXPLAIN'] = 'Tästä ohjauspaneelista voit lisätä, muokata ja poistaa sanoja, jotka on automaattisesti sensuroitu teidän foorumeilla. Lisäksi ihmiset eivät saa rekisteröityä käyttäjätunnukset, jotka sisältävät nämä sanat. Yleismerkkejä (*) hyväksytään sana-kenttään. Esimerkiksi, *testi* vastaa inhottavia, testi* sopisi testaus *testi sopisi inhoavat.';
$lang['WORD'] = 'Sana';
$lang['EDIT_WORD_CENSOR'] = 'Muokkaa sanaa sensuroida';
$lang['REPLACEMENT'] = 'Vaihto';
$lang['ADD_NEW_WORD'] = 'Lisää uusi sana';
$lang['UPDATE_WORD'] = 'Päivitys sanaa sensuroida';

$lang['MUST_ENTER_WORD'] = 'Sinun on kirjoita sana ja sen korvaaminen';
$lang['NO_WORD_SELECTED'] = 'Ei sana valittu muokattavaksi';

$lang['WORD_UPDATED'] = 'Valitun sanan sensuroida on päivitetty onnistuneesti';
$lang['WORD_ADDED'] = 'Sana sensori on lisätty onnistuneesti';
$lang['WORD_REMOVED'] = 'Valitun sanan sensori on poistettu onnistuneesti ';

$lang['CLICK_RETURN_WORDADMIN'] = 'Klikkaa %sHere%s palata Sanaa Sensuroida Hallinto';

// Mass Email
$lang['MASS_EMAIL_EXPLAIN'] = 'Täällä voit lähettää viestin joko kaikki käyttäjät tai kaikki käyttäjät tietyn ryhmän. Voit tehdä tämän, sähköpostia lähetetään ulos hallinnollinen sähköpostiosoite mukana, blind carbon copy lähetetään kaikille vastaanottajille. Jos olet sähköpostitse suuri joukko ihmisiä ole kärsivällinen lähettämisen jälkeen ja älä lopeta sivun puolivälissä. Se on normaalia, että massa sähköpostitse kestää kauan ja saat ilmoituksen, kun käsikirjoitus on valmistunut';
$lang['COMPOSE'] = 'Säveltää';

$lang['RECIPIENTS'] = 'Vastaanottajat';
$lang['ALL_USERS'] = 'Kaikki Käyttäjät';

$lang['MASS_EMAIL_MESSAGE_TYPE'] = 'Sähköpostin tyyppi';

$lang['EMAIL_SUCCESSFULL'] = 'Viestisi on lähetetty';
$lang['CLICK_RETURN_MASSEMAIL'] = 'Klikkaa %sHere%s palata Massa Sähköposti muodossa';

// Ranks admin
$lang['RANKS_TITLE'] = 'Listalla Hallinto';
$lang['RANKS_EXPLAIN'] = 'Tällä lomakkeella voit lisätä, muokata, tarkastella ja poistaa riveissä. Voit myös luoda mukautettuja joukkoon, joita voidaan soveltaa käyttäjän kautta käyttäjän hallinta laitos';

$lang['ADD_NEW_RANK'] = 'Lisää uusi listalla';
$lang['RANK_TITLE'] = 'Sijoitus Nimi';
$lang['STYLE_COLOR'] = 'Tyyli listalla';
$lang['STYLE_COLOR_FAQ'] = 'Määritä class for maalaus otsikko haluamasi väri. Esimerkiksi <i class="bold">colorAdmin<i>';
$lang['RANK_IMAGE'] = 'Sijoitus Kuva';
$lang['RANK_IMAGE_EXPLAIN'] = 'Käytä tätä määritellä pieni kuva, joka liittyy listalla';

$lang['MUST_SELECT_RANK'] = 'Sinun täytyy valitse sijoitus';
$lang['NO_ASSIGNED_RANK'] = 'Ei erityisiä sijoitus määritetty';

$lang['RANK_UPDATED'] = 'Listalla on päivitetty onnistuneesti';
$lang['RANK_ADDED'] = 'Listalla on lisätty onnistuneesti';
$lang['RANK_REMOVED'] = 'Listalla on poistettu onnistuneesti';
$lang['NO_UPDATE_RANKS'] = 'Listalla on poistettu onnistuneesti. Kuitenkin, käyttäjä tilejä käyttäen tämä sijoitus ei ole päivitetty. Sinun täytyy manuaalisesti nollata listalla nämä tilit';

$lang['CLICK_RETURN_RANKADMIN'] = 'Klikkaa %sHere%s palata Listalla Hallinto';

// Disallow Username Admin
$lang['DISALLOW_CONTROL'] = 'Käyttäjätunnus Estää Ohjaus';
$lang['DISALLOW_EXPLAIN'] = 'Täällä voit hallita käyttäjätunnuksia, joita ei saa käyttää. Kielletty käyttäjänimet voivat sisältää jokerimerkkiä *. Huomaa, että et saa määrittää käyttäjätunnuksen, joka on jo rekisteröity. Sinun täytyy ensin poistaa se nimi sitten estää se.';

$lang['DELETE_DISALLOW'] = 'Poista';
$lang['DELETE_DISALLOW_TITLE'] = 'Poista Kieltänyt Käyttäjätunnus';
$lang['DELETE_DISALLOW_EXPLAIN'] = 'Voit poistaa kieltänyt käyttäjätunnuksen valitsemalla käyttäjätunnus tästä luettelosta ja klikkaamalla lähetä';

$lang['ADD_DISALLOW'] = 'Lisää';
$lang['ADD_DISALLOW_TITLE'] = 'Lisää kieltänyt käyttäjätunnus';

$lang['NO_DISALLOWED'] = 'Ei Kieltänyt Käyttäjätunnuksia';

$lang['DISALLOWED_DELETED'] = 'Sen kieltänyt käyttäjätunnus on poistettu onnistuneesti';
$lang['DISALLOW_SUCCESSFUL'] = 'Sen kieltänyt käyttäjätunnus on lisätty onnistuneesti';
$lang['DISALLOWED_ALREADY'] = 'Antamasi nimi voisi olla kielletty. Se joko on jo olemassa lista, on sana sensuroida lista tai vastaava käyttäjätunnus on läsnä.';

$lang['CLICK_RETURN_DISALLOWADMIN'] = 'Klikkaa %sHere%s palata Estää Käyttäjätunnus Hallinto';

// Version Check
$lang['VERSION_INFORMATION'] = 'Version Tiedot';
$lang['UPDATE_AVAILABLE'] = 'Update available';
$lang['CHANGELOG'] = 'Changelog';

// Login attempts configuration
$lang['MAX_LOGIN_ATTEMPTS'] = 'Sallittu kirjautumisyritysten';
$lang['MAX_LOGIN_ATTEMPTS_EXPLAIN'] = 'Sallittu määrä hallituksen kirjautumisyrityksiä.';
$lang['LOGIN_RESET_TIME'] = 'Kirjaudu lukon aika';
$lang['LOGIN_RESET_TIME_EXPLAIN'] = 'Minuutin ajan, käyttäjän on odotettava, kunnes hän saa kirjautua uudelleen kun yli sallittu määrä kirjautumisyrityksiä.';

// Permissions List
$lang['PERMISSIONS_LIST'] = 'Käyttöoikeudet-Luettelosta';
$lang['AUTH_CONTROL_CATEGORY'] = 'Luokka Käyttöoikeudet Ohjaus';
$lang['FORUM_AUTH_LIST_EXPLAIN'] = 'Tämä tarjoaa yhteenveto lupa tasot kunkin foorumi. Voit muokata näitä oikeuksia käyttämällä joko yksinkertainen tai kehittynyt menetelmä klikkaamalla foorumin nimeä. Muista, että muuttamalla lupaa tason foorumeilla vaikuttaa, jotka käyttäjät voivat suorittaa erilaisia toimintoja niiden sisällä.';
$lang['CAT_AUTH_LIST_EXPLAIN'] = 'Tämä tarjoaa yhteenveto lupa tasot kunkin foorumin puitteissa tässä luokassa. Voit muokata käyttöoikeuksia yksittäisten foorumeilla, joko yksinkertainen tai kehittynyt menetelmä klikkaamalla foorumin nimeä. Vaihtoehtoisesti, voit asettaa käyttöoikeudet kaikille foorumeilla tähän luokkaan käyttämällä avattavasta valikot alareunassa sivun. Muista, että muuttamalla lupaa tason foorumeilla vaikuttaa, jotka käyttäjät voivat suorittaa erilaisia toimintoja niiden sisällä.';
$lang['FORUM_AUTH_LIST_EXPLAIN_ALL'] = 'Kaikki käyttäjät';
$lang['FORUM_AUTH_LIST_EXPLAIN_REG'] = 'Kaikki rekisteröityneet käyttäjät';
$lang['FORUM_AUTH_LIST_EXPLAIN_PRIVATE'] = 'Vain käyttäjät, myönnetty erityistä lupaa';
$lang['FORUM_AUTH_LIST_EXPLAIN_MOD'] = 'Vain moderaattorit tällä foorumilla';
$lang['FORUM_AUTH_LIST_EXPLAIN_ADMIN'] = 'Vain järjestelmänvalvojat';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_VIEW'] = '%s voi tarkastella tällä foorumilla';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_READ'] = '%s voi lukea viestejä tässä foorumissa';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_POST'] = '%s voi lähettää tällä foorumilla';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_REPLY'] = '%s voi vastata viesteihin tässä foorumissa';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_EDIT'] = '%s voi muokata viestejäsi tässä foorumissa';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_DELETE'] = '%s voi poistaa viestejäsi tässä foorumissa';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_STICKY'] = '%s voi lähettää tahmea aiheita tällä foorumilla';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_ANNOUNCE'] = '%s voivat lähettää ilmoitukset tässä foorumissa';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_VOTE'] = '%s voi äänestää tässä foorumissa';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_POLLCREATE'] = '%s voi luoda äänestyksiä tällä foorumilla';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_ATTACHMENTS'] = '%s voi lähettää liitetiedostoja';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_DOWNLOAD'] = '%s voi ladata liitetiedostoja';

// Misc
$lang['SF_SHOW_ON_INDEX'] = 'Näyttää pääsivulla';
$lang['SF_PARENT_FORUM'] = 'Vanhempi forum';
$lang['SF_NO_PARENT'] = 'Ei vanhemman forum';
$lang['TEMPLATE'] = 'Malli';
$lang['SYNC'] = 'Sync';

// Mods
$lang['MAX_NEWS_TITLE'] = 'Max. pituus uutiset';
$lang['NEWS_COUNT'] = 'Kuinka monet uutiset osoittavat';
$lang['NEWS_FORUM_ID'] = 'Mitä foorumeilla näyttää <br /> <h6>Of eri foorumeilla esiin, pilkulla erotettuna. Esimerkiksi 1,2,3</h6>';
$lang['NOAVATAR'] = 'Ei avatar';
$lang['TRACKER_STATS'] = 'Tilastot tracker';
$lang['WHOIS_INFO'] = 'Tietoa IP-osoite';
$lang['SHOW_MOD_HOME_PAGE'] = 'Näytä, että moderaattorit index.php';
$lang['SHOW_BOARD_STARTED_INDEX'] = 'Show board start date on index.php';
$lang['PREMOD_HELP'] = '<h4><span class="tor-icon tor-dup">&#8719;</span> Pre-moderation</h4> <h6>If sinulla ei ole jakaumat tilan v, #, tai T tässä osassa, mukaan lukien kohdissa, jakelu saavat automaattisesti tämän status</h6>';
$lang['TOR_COMMENT'] = '<h4>Kommentti jakelun tilasta</h4> <h6>Kommentin avulla voit huomauttaa tehdyistä virheistä irtisanojalle. Jos tilat ovat epätäydellisiä, julkaisupäällikkö voi korjata julkaisun</h6>';
$lang['SEED_BONUS_ADD'] = '<h4>Adding siemen bonus </h4> <h6> Määrä jakaumat ovat jakoi käyttäjän ja koko bonukset niitä (latauksen kertaa tunnissa) </h6>';
$lang['SEED_BONUS_RELEASE'] = 'N-määrä tiedotteet';
$lang['SEED_BONUS_POINTS'] = 'bonukset tunnissa';
$lang['SEED_BONUS_TOR_SIZE'] = '<h4>Minimum jakelu, joka jaetaan bonuksia </h4> <h6> Jos haluat laskea bonuksia kaikki jakelu, jätä tyhjäksi. </h6>';
$lang['SEED_BONUS_USER_REGDATA'] = '<h4>Minimum pituus käyttäjän tracker, jonka jälkeen palkitaan bonuksia </h4> <h6> Jos haluat kertyy bonuksia kaikille käyttäjille, jätä tyhjäksi. </h6>';
$lang['SEED_BONUS_WARNING'] = 'HUOMIO! Siemen Bonuksia pitäisi olla nousevassa järjestyksessä';
$lang['SEED_BONUS_EXCHANGE'] = 'Konfigurointi Exchange Sid Bonukset';
$lang['SEED_BONUS_ROPORTION'] = 'Osuus lisäksi vaihto bonuksia GB';

// Modules, this replaces the keys used
$lang['CONTROL_PANEL'] = 'Ohjauspaneeli';
$lang['SHADOW_ATTACHMENTS'] = 'Varjo Liitteet';
$lang['FORBIDDEN_EXTENSIONS'] = 'Kielletty Laajennukset';
$lang['EXTENSION_CONTROL'] = 'Extension Control';
$lang['EXTENSION_GROUP_MANAGE'] = 'Laajennus Ryhmien Ohjaus';
$lang['SPECIAL_CATEGORIES'] = 'Erityistä Luokat';
$lang['SYNC_ATTACHMENTS'] = 'Synkronoida Liitteet';
$lang['QUOTA_LIMITS'] = 'Kiintiön Rajoissa';

// Attachments -> Management
$lang['ATTACH_SETTINGS'] = 'Kiinnitys Asetukset';
$lang['MANAGE_ATTACHMENTS_EXPLAIN'] = 'Täällä voit määrittää Tärkeimmät Asetukset Liite Mod. Jos painat Testin Asetukset-Painiketta, Kiinnitys Mod ei muutaman Järjestelmä Testit, voit olla varma, että Mod toimii oikein. Jos sinulla on ongelmia lataamalla Tiedostoja, suorita tämä Testi, saada yksityiskohtaista error-viesti.';
$lang['ATTACH_FILESIZE_SETTINGS'] = 'Kiinnitys Filesize Asetukset';
$lang['ATTACH_NUMBER_SETTINGS'] = 'Liite Numero Asetukset';
$lang['ATTACH_OPTIONS_SETTINGS'] = 'Kiinnitys Vaihtoehtoja';

$lang['UPLOAD_DIRECTORY'] = 'Upload Hakemistoon';
$lang['UPLOAD_DIRECTORY_EXPLAIN'] = 'Kirjoita suhteellinen polku teidän TorrentPier asennus Liitetiedostoja ladata hakemistoon. Esimerkiksi, kirjoita "files", jos TorrentPier Asennus sijaitsee https://www.yourdomain.com/torrentpier ja Kiinnitys Upload Hakemistoon sijaitsee https://www.yourdomain.com/torrentpier/files.';
$lang['ATTACH_IMG_PATH'] = 'Liitetiedoston Lähettämistä Kuvaketta';
$lang['ATTACH_IMG_PATH_EXPLAIN'] = 'Tämän Kuvan vieressä näkyy Liitetiedostona Linkkejä yksittäisiä Viestejä. Jätä tämä kenttä tyhjäksi, jos et halua kuvaketta näytetään. Tämä Asetus korvataan Asetukset Laajennus Ryhmien Hallinta.';
$lang['ATTACH_TOPIC_ICON'] = 'Kiinnitys Aihe Kuvake';
$lang['ATTACH_TOPIC_ICON_EXPLAIN'] = 'Tämä Kuva on näytössä, ennen kuin aiheet, joissa on Liitteitä. Jätä tämä kenttä tyhjäksi, jos et halua kuvaketta näytetään.';
$lang['ATTACH_DISPLAY_ORDER'] = 'Kiinnitys Näyttö, Jotta';
$lang['ATTACH_DISPLAY_ORDER_EXPLAIN'] = 'Täällä voit valita, näytetäänkö Liitetiedostoja Viestit/PMs Alenevassa Filetime Järjestyksessä (Uusin Kiinnitys Ensin) tai Nouseva Filetime Järjestyksessä (Vanhin Kiinnitys Ensin).';
$lang['SHOW_APCP'] = 'Käytä uutta ohjauspaneelin sovelluksia';
$lang['SHOW_APCP_EXPLAIN'] = 'Valitse, haluatko käyttää erillisen ohjauspaneelin sovellukset (kyllä), tai vanha menetelmä, jossa on kaksi laatikkoa sovellukset ja editointi sovelluksia (ei mitään) viestikenttään. Sitä on vaikea selittää, miltä se näyttää, niin kokeile itse.';

$lang['MAX_FILESIZE_ATTACH'] = 'Filesize';
$lang['MAX_FILESIZE_ATTACH_EXPLAIN'] = 'Maksimi tiedostokoko Liitetiedostoja. Arvo 0 tarkoittaa \'rajaton\'. Tämä Asetus on rajoitettu Palvelimen Kokoonpano. Esimerkiksi, jos php Kokoonpano sallii vain enintään 2 MB lisäykset, tämä voi olla korvataan Mod.';
$lang['ATTACH_QUOTA'] = 'Kiinnitys Kiintiö';
$lang['ATTACH_QUOTA_EXPLAIN'] = 'Suurin levytilaa KAIKKI Liitteet voi pitää teidän Webspace. Arvo 0 tarkoittaa \'rajaton\'.';
$lang['MAX_FILESIZE_PM'] = 'Maksimi Tiedostokoko Yksityiset Viestit-Kansioon';
$lang['MAX_FILESIZE_PM_EXPLAIN'] = 'Suurin levytilaa Liitetiedostoja voi käyttää kunkin Käyttäjän Yksityinen Viesti ruutuun. Arvo 0 tarkoittaa \'rajaton\'.';
$lang['DEFAULT_QUOTA_LIMIT'] = 'Default Quota Limit';
$lang['DEFAULT_QUOTA_LIMIT_EXPLAIN'] = 'Täällä voit valita Oletuksena Kiintiö Rajoittaa automaattisesti ensirekisteröityjen Käyttäjät ja Käyttäjät, joilla on määritelty Kiintiön rajoissa. Vaihtoehto \'Ei Kiintiötä Raja\' on, joka ei käytä mitään Kiinnitys Kiintiöitä, sen sijaan käyttää default-Asetukset on määritetty tämän Johdon Paneeli.';

$lang['MAX_ATTACHMENTS'] = 'Enimmäismäärä Liitteet';
$lang['MAX_ATTACHMENTS_EXPLAIN'] = 'Enimmäismäärä liitteet sallittu yksi viesti.';
$lang['MAX_ATTACHMENTS_PM'] = 'Suurin määrä Liitteitä yksi Yksityinen Viesti';
$lang['MAX_ATTACHMENTS_PM_EXPLAIN'] = 'Määritä enimmäismäärä liitetiedostoja käyttäjä ei saa sisällyttää yksityisen viestin.';

$lang['DISABLE_MOD'] = 'Poistaa Liitetiedoston Mod';
$lang['DISABLE_MOD_EXPLAIN'] = 'Tämä vaihtoehto on lähinnä testata uusia malleja tai teemoja, se poistaa kaikki Kiinnitys Toiminnot, paitsi Admin Paneeli.';
$lang['PM_ATTACHMENTS'] = 'Salli Liitetiedostoja Yksityisiä Viestejä';
$lang['PM_ATTACHMENTS_EXPLAIN'] = 'Salli/Estä liittämällä tiedostot Yksityisiä Viestejä.';
$lang['ATTACHMENT_TOPIC_REVIEW'] = 'Näytä sovelluksia, tarkastelu, viestinnän aiheita, kun kirjoitat vastauksen?';
$lang['ATTACHMENT_TOPIC_REVIEW_EXPLAIN'] = 'Jos laittaa "kyllä", kaikki sovellukset näytetään tarkastelua viestinnän aiheista.';

// Attachments -> Shadow Attachments
$lang['SHADOW_ATTACHMENTS_EXPLAIN'] = 'Täällä voit poistaa liitetiedoston tietoja viestejä, kun tiedostoja puuttuu teidän tiedostojärjestelmä, ja poistaa tiedostoja, jotka eivät ole enää kiinnitetty mitään viestejä. Voit ladata tai katsella tiedostoa, jos et klikkaa sitä; jos yhteys on läsnä, tiedostoa ei ole olemassa.';
$lang['SHADOW_ATTACHMENTS_FILE_EXPLAIN'] = 'Poista kaikki liitteet tiedostoja, jotka ovat olemassa teidän tiedostojärjestelmä ja ei ole osoitettu olemassa olevaan virkaan.';
$lang['SHADOW_ATTACHMENTS_ROW_EXPLAIN'] = 'Poista kaikki lähettämistä kiinnitys tiedot-tiedostoja, jotka eivät ole teidän tiedostojärjestelmä.';
$lang['EMPTY_FILE_ENTRY'] = 'Tyhjä Tiedosto Merkintä';

// Attachments -> Sync
$lang['SYNC_THUMBNAIL_RESETTED'] = 'Pientä resetted Kiinnitys: %s'; // replace %s with physical Filename
$lang['ATTACH_SYNC_FINISHED'] = 'Liitteen synkronointi valmis.';
$lang['SYNC_TOPICS'] = 'Sync-Aiheet';
$lang['SYNC_POSTS'] = 'Synkronoi Viestit';
$lang['SYNC_THUMBNAILS'] = 'Sync Pikkukuvat';

// Extensions -> Extension Control
$lang['MANAGE_EXTENSIONS'] = 'Hallita Laajennuksia';
$lang['MANAGE_EXTENSIONS_EXPLAIN'] = 'Täällä voit hallita tiedostopäätteet. Jos haluat sallia/estää Laajennus ladataan, käytä Laajennus Ryhmien Hallinta.';
$lang['EXPLANATION'] = 'Selitys';
$lang['EXTENSION_GROUP'] = 'Laajennus Ryhmä';
$lang['INVALID_EXTENSION'] = 'Virheellinen Tiedostopääte';
$lang['EXTENSION_EXIST'] = 'Laajennus %s jo olemassa'; // replace %s with the Extension
$lang['UNABLE_ADD_FORBIDDEN_EXTENSION'] = 'Laajennus %s on kielletty, et voi lisätä sen saa Laajennukset'; // replace %s with Extension

// Extensions -> Extension Groups Management
$lang['MANAGE_EXTENSION_GROUPS'] = 'Hallita Tiedostotunnistetta Ryhmät';
$lang['MANAGE_EXTENSION_GROUPS_EXPLAIN'] = 'Täällä voit lisätä, poistaa ja muokata Laajennus Ryhmät, voit poistaa Laajennus Ryhmät, määrittää erityinen Luokka, niitä, muuttaa lataus mekanismi ja voit määrittää Lähetyksen Kuvake, joka näkyy edessä Kiinnitys Ryhmään.';
$lang['SPECIAL_CATEGORY'] = 'Special Category';
$lang['CATEGORY_IMAGES'] = 'Kuvia';
$lang['ALLOWED'] = 'Sallittu';
$lang['ALLOWED_FORUMS'] = 'Saa Foorumeilla';
$lang['EXT_GROUP_PERMISSIONS'] = 'Ryhmän Käyttöoikeudet';
$lang['DOWNLOAD_MODE'] = 'Lataa Tilassa';
$lang['UPLOAD_ICON'] = 'Lataa Ikoni';
$lang['MAX_GROUPS_FILESIZE'] = 'Maksimi Tiedostokoko';
$lang['EXTENSION_GROUP_EXIST'] = 'Laajennus Ryhmä %s jo olemassa'; // replace %s with the group name

// Extensions -> Special Categories
$lang['MANAGE_CATEGORIES'] = 'Hallitse Erityistä Luokat';
$lang['MANAGE_CATEGORIES_EXPLAIN'] = 'Here you can configure the Special Categories. You can set up Special Parameters and Conditions for the Special Categories assigned to an Extension Group.';
$lang['SETTINGS_CAT_IMAGES'] = 'Asetukset Special Luokka: Kuvat';
$lang['SETTINGS_CAT_FLASH'] = 'Asetukset Special Category: Flash-Tiedostoja';
$lang['DISPLAY_INLINED'] = 'Näyttää Kuvien Sisältöön';
$lang['DISPLAY_INLINED_EXPLAIN'] = 'Valitse näytetäänkö kuvia suoraan sisällä postitse (kyllä) tai voit näyttää kuvat linkkinä ?';
$lang['MAX_IMAGE_SIZE'] = 'Suurin Kuvan Mitat';
$lang['MAX_IMAGE_SIZE_EXPLAIN'] = 'Täällä voit määrittää suurin sallittu Kuvan Dimensio on liitteenä (Leveys x Korkeus kuvapisteinä).<br />If se on asetettu 0x0, tämä ominaisuus on poistettu käytöstä. Joitakin Kuvia, tämä Ominaisuus ei toimi, koska rajoitukset in PHP.';
$lang['IMAGE_LINK_SIZE'] = 'Kuvan Linkki Mitat';
$lang['IMAGE_LINK_SIZE_EXPLAIN'] = 'Jos tämä on määritelty Ulottuvuus Kuva on saavutettu, Kuva näkyy linkkinä, eikä se näytetään sisältöön,<br />if Inline Näkymä on käytössä (Leveys x Korkeus kuvapisteinä).<br />If se on asetettu 0x0, tämä ominaisuus on poistettu käytöstä. Joitakin Kuvia, tämä Ominaisuus ei toimi, koska rajoitukset in PHP.';
$lang['ASSIGNED_GROUP'] = 'Valittu Ryhmä';

$lang['IMAGE_CREATE_THUMBNAIL'] = 'Luo Thumbnail';
$lang['IMAGE_MIN_THUMB_FILESIZE'] = 'Pienin Pikkukuva Filesize';
$lang['IMAGE_MIN_THUMB_FILESIZE_EXPLAIN'] = 'Jos Kuva on pienempi kuin tämä on määritelty Tiedostokoko, ei Thumbnail luodaan, koska se on tarpeeksi pieni.';

// Extensions -> Forbidden Extensions
$lang['MANAGE_FORBIDDEN_EXTENSIONS'] = 'Hallita Kielletty Laajennukset';
$lang['MANAGE_FORBIDDEN_EXTENSIONS_EXPLAIN'] = 'Täällä voit lisätä tai poistaa kielletty laajennukset. Laajennukset php, php3 ja php4 on kielletty oletusarvoisesti turvallisuussyistä, et voi poistaa niitä.';
$lang['FORBIDDEN_EXTENSION_EXIST'] = 'Kielletty Tiedostotunnistetta %s jo olemassa'; // replace %s with the extension
$lang['EXTENSION_EXIST_FORBIDDEN'] = 'Laajennus %s on määritelty sallitut Laajennukset, poista se ennen kuin lisäät sen tänne.'; // replace %s with the extension

// Extensions -> Extension Groups Control -> Group Permissions
$lang['GROUP_PERMISSIONS_TITLE_ADMIN'] = 'Laajennus-Ryhmän Käyttöoikeudet -> \'%s\''; // Replace %s with the Groups Name
$lang['GROUP_PERMISSIONS_EXPLAIN'] = 'Täällä voit rajoittaa valitun Tiedostotunnistetta Ryhmä Foorumeilla valinta (määritellään Sallittu Foorumeilla. Laatikko). Oletusarvo on, että Tiedostotunnistetta Ryhmien kaikki Foorumit Käyttäjä voi Liittää Tiedostoja (normaalisti Kiinnitys Mod teki sen alusta). Lisää vain näitä Foorumeita haluat Laajennus Ryhmä (Extensions tämän Ryhmän sisällä) saa siellä, oletuksena KAIKKI FOORUMIT katoaa, kun lisäät Foorumeilla Luetteloon. Voit lisätä uudelleen KAIKKI FOORUMIT milloin tahansa. Jos lisäät foorumi teidän Aluksella ja Lupa on asettaa KAIKKI FOORUMIT mikään ei muutu. Mutta, jos olet muuttanut ja rajoitettu pääsy tiettyihin Foorumeilla, sinun täytyy tarkistaa takaisin tänne lisätä juuri luotu Foorumi. Se on helppo tehdä tämän automaattisesti, mutta tämä pakottaa voit muokata joukko Tiedostoja, siksi olen valinnut sellaiseksi kuin se on nyt. Pidä mielessä, että kaikki Foorumit on lueteltu tässä.';
$lang['NOTE_ADMIN_EMPTY_GROUP_PERMISSIONS'] = 'HUOMAUTUS:<br />Within alla luetellut Foorumeilla Käyttäjät eivät yleensä saa liittää tiedostoja, mutta koska ei Tiedostotunnistetta Ryhmä ei saa olla kiinnitetty, sinun Käyttäjät eivät voi liittää mitään. Jos he yrittävät, he saavat virheilmoituksia. Ehkä haluat asettaa Lupaa \'Post Tiedostot\' ADMIN näillä Foorumeilla.<br /><br />';
$lang['ADD_FORUMS'] = 'Lisää Foorumeilla';
$lang['ADD_SELECTED'] = 'Lisää Valitut';
$lang['PERM_ALL_FORUMS'] = 'KAIKKI FOORUMIT';

// Attachments -> Quota Limits
$lang['MANAGE_QUOTAS'] = 'Hallita Kiinnitys Kiintiön Rajoissa';
$lang['MANAGE_QUOTAS_EXPLAIN'] = 'Täällä voit lisätä/poistaa/muuttaa Kiintiön Rajoissa. Voit määrittää nämä Kiintiön Rajat Käyttäjät ja Ryhmät myöhemmin. Määrittää Kiintiön Raja-Käyttäjä, sinun täytyy mennä Käyttäjät->Hallinta, valitse Käyttäjä, ja näet Vaihtoehtoja alareunassa. Määrittää Kiintiön Raja-Ryhmään, siirry Ryhmät->Hallinta, valitse Ryhmä, jota haluat muokata, ja näet Asetukset. Jos haluat nähdä, mitkä Käyttäjät ja Ryhmät on määritetty tietyn Kiintiön rajoissa, klikkaa "Näytä" vasemmalla Kiintiön Kuvaus.';
$lang['ASSIGNED_USERS'] = 'Kohdistetut Käyttäjät';
$lang['ASSIGNED_GROUPS'] = 'Osoitettu Ryhmät';
$lang['QUOTA_LIMIT_EXIST'] = 'Kiintiön Raja %s on jo olemassa.'; // Replace %s with the Quota Description

// Attachments -> Control Panel
$lang['CONTROL_PANEL_TITLE'] = 'Liitetiedoston Ohjauspaneeli';
$lang['CONTROL_PANEL_EXPLAIN'] = 'Täällä voit tarkastella ja hallita kaikkia liitetiedostoja perustuu Käyttäjien, Liitetiedostoja, Näkemyksiä jne...';
$lang['FILECOMMENT'] = 'Tiedoston Kommentti';

// Control Panel -> Search
$lang['SEARCH_WILDCARD_EXPLAIN'] = 'Käyttää * - merkkiä yleismerkkinä osittaista ottelut';
$lang['SIZE_SMALLER_THAN'] = 'Liitetiedoston koko on pienempi kuin (tavua)';
$lang['SIZE_GREATER_THAN'] = 'Liitetiedoston koko on suurempi kuin (tavua)';
$lang['COUNT_SMALLER_THAN'] = 'Lataa määrä on pienempi kuin';
$lang['COUNT_GREATER_THAN'] = 'Lataa määrä on suurempi kuin';
$lang['MORE_DAYS_OLD'] = 'Enemmän kuin tämä, monta päivää vanha';
$lang['NO_ATTACH_SEARCH_MATCH'] = 'Ei Liitteitä tavannut hakuehdot';

// Control Panel -> Statistics
$lang['NUMBER_OF_ATTACHMENTS'] = 'Liitteiden määrä';
$lang['TOTAL_FILESIZE'] = 'Yhteensä Filesize';
$lang['NUMBER_POSTS_ATTACH'] = 'Useita Viestit, joissa on Liitteitä';
$lang['NUMBER_TOPICS_ATTACH'] = 'Useita Aiheita, joissa on Liitteitä';
$lang['NUMBER_USERS_ATTACH'] = 'Riippumattomien Käyttäjien Lähetetty Liitteet';
$lang['NUMBER_PMS_ATTACH'] = 'Kokonaismäärä Liitteet Yksityisiä Viestejä';
$lang['ATTACHMENTS_PER_DAY'] = 'Liitteet per päivä';

// Control Panel -> Attachments
$lang['STATISTICS_FOR_USER'] = 'Kiinnitys Tilastot %s'; // replace %s with username
$lang['DOWNLOADS'] = 'Lataukset';
$lang['POST_TIME'] = 'Post Aikaa';
$lang['POSTED_IN_TOPIC'] = 'Lähetetty Aihe';
$lang['SUBMIT_CHANGES'] = 'Lähetä Muutokset';

// Sort Types
$lang['SORT_ATTACHMENTS'] = 'Liitteet';
$lang['SORT_SIZE'] = 'Koko';
$lang['SORT_FILENAME'] = 'Tiedostonimi';
$lang['SORT_COMMENT'] = 'Kommentti';
$lang['SORT_EXTENSION'] = 'Laajennus';
$lang['SORT_DOWNLOADS'] = 'Lataukset';
$lang['SORT_POSTTIME'] = 'Post Aikaa';

// View Types
$lang['VIEW_STATISTIC'] = 'Tilastot';
$lang['VIEW_SEARCH'] = 'Haku';
$lang['VIEW_USERNAME'] = 'Käyttäjätunnus';
$lang['VIEW_ATTACHMENTS'] = 'Liitteet';

// Successfully updated
$lang['ATTACH_CONFIG_UPDATED'] = 'Kiinnitys Kokoonpano päivitetty onnistuneesti';
$lang['CLICK_RETURN_ATTACH_CONFIG'] = 'Klikkaa %sHere%s palata Kiinnitys Kokoonpano';
$lang['TEST_SETTINGS_SUCCESSFUL'] = 'Asetukset Testi on päättynyt, kokoonpano näyttää olevan kunnossa.';

// Some basic definitions
$lang['ATTACHMENTS'] = 'Liitteet';
$lang['EXTENSIONS'] = 'Laajennukset';
$lang['EXTENSION'] = 'Laajennus';

$lang['RETURN_CONFIG'] = '%sReturn että Configuration%s';
$lang['CONFIG_UPD'] = 'Kokoonpano Päivitetty Onnistuneesti';
$lang['SET_DEFAULTS'] = 'Palauta oletukset';

// Forum config
$lang['FORUM_CFG_EXPL'] = 'Forum config';

$lang['BT_SELECT_FORUMS'] = 'Forum vaihtoehdoista:';
$lang['BT_SELECT_FORUMS_EXPL'] = 'pitämällä <i>Ctrl</i>, kun valitset useita foorumeilla';

$lang['REG_TORRENTS'] = 'Rekisteröidy torrentit';
$lang['DISALLOWED'] = 'Kielletty';
$lang['ALLOW_REG_TRACKER'] = 'Saa foorumeita rekisteröitymättä .torrent tracker';
$lang['ALLOW_PORNO_TOPIC'] = 'Saa lähettää sisältöä 18+';
$lang['SHOW_DL_BUTTONS'] = 'Näytä painikkeet manuaalisesti muuttamalla DL-asema';
$lang['SELF_MODERATED'] = 'Käyttäjät voivat <b>move</b> niiden aiheita toisella foorumilla';

$lang['BT_ANNOUNCE_URL_HEAD'] = 'Ilmoittaa URL-osoitteen';
$lang['BT_ANNOUNCE_URL'] = 'Ilmoittaa url-osoitteen';
$lang['BT_ANNOUNCE_URL_EXPL'] = 'voit määrittää tiedostojen sallittu url-osoitteita "includes/torrent_announce_urls.php"';
$lang['BT_DISABLE_DHT'] = 'Poistaa DHT-verkko';
$lang['BT_DISABLE_DHT_EXPL'] = 'Poistaa Peer Vaihtoa ja DHT (suositellaan yksityisiä verkkoja, vain url-osoite ilmoittaa)';
$lang['BT_PRIVATE_TRACKER'] = 'This tracker is private: file listing (for guests), DHT | PEX are disabled';
$lang['BT_PRIVATE_TORRENT'] = 'The creator of this torrent made it private';
$lang['BT_CHECK_ANNOUNCE_URL'] = 'Varmista, ilmoittaa url-osoitteen';
$lang['BT_CHECK_ANNOUNCE_URL_EXPL'] = 'rekisteröityä tracker sallittu vain url-osoitteita';
$lang['BT_REPLACE_ANN_URL'] = 'Vaihda ilmoittaa url-osoitteen';
$lang['BT_REPLACE_ANN_URL_EXPL'] = 'korvaa alkuperäisen ilmoittaa url-osoite oletuksena .torrent-tiedostoja';
$lang['BT_DEL_ADDIT_ANN_URLS'] = 'Poista kaikki ylimääräiset ilmoittaa url-osoitteita';
$lang['BT_DEL_ADDIT_ANN_URLS_EXPL'] = 'jos torrent-sisältää osoitteet muita seurantoja, ne poistetaan';

$lang['BT_SHOW_PEERS_HEAD'] = 'Ikäisensä-Lista';
$lang['BT_SHOW_PEERS'] = 'Näyttää ikäisensä (kylvökoneet ja leechers)';
$lang['BT_SHOW_PEERS_EXPL'] = 'tämä näyttää kylvökoneet/leechers luettelo edellä aiheen kanssa torrent';
$lang['BT_SHOW_PEERS_MODE'] = 'Oletuksena näyttää ikäisensä, kuten:';
$lang['BT_SHOW_PEERS_MODE_COUNT'] = 'Laskea vain';
$lang['BT_SHOW_PEERS_MODE_NAMES'] = 'Nimiä vain';
$lang['BT_SHOW_PEERS_MODE_FULL'] = 'Täydelliset tiedot';
$lang['BT_ALLOW_SPMODE_CHANGE'] = 'Salli "Full details" - tilassa';
$lang['BT_ALLOW_SPMODE_CHANGE_EXPL'] = 'jos "ei", vain oletus peer-näyttö-tila ei ole käytettävissä';
$lang['BT_SHOW_IP_ONLY_MODER'] = 'Vertaistensa <b>IP</b>s ovat näkyvissä vain moderaattorit';
$lang['BT_SHOW_PORT_ONLY_MODER'] = 'Vertaistensa <b>Port</b>s ovat näkyvissä vain moderaattorit';

$lang['BT_SHOW_DL_LIST_HEAD'] = 'DL-Lista';
$lang['BT_SHOW_DL_LIST'] = 'Näytä DL-Luettelosta Lataa aiheita';
$lang['BT_DL_LIST_ONLY_1ST_PAGE'] = 'Näytä DL-Listalla vain ensimmäisen sivun aiheita';
$lang['BT_DL_LIST_ONLY_COUNT'] = 'Näytä vain määrä käyttäjiä';
$lang['BT_SHOW_DL_LIST_BUTTONS'] = 'Näytä painikkeet manuaalisesti muuttamalla DL-asema';
$lang['BT_SHOW_DL_BUT_WILL'] = $lang['DLWILL'];
$lang['BT_SHOW_DL_BUT_DOWN'] = $lang['DLDOWN'];
$lang['BT_SHOW_DL_BUT_COMPL'] = $lang['DLCOMPLETE'];
$lang['BT_SHOW_DL_BUT_CANCEL'] = $lang['DLCANCEL'];

$lang['BT_ADD_AUTH_KEY_HEAD'] = 'Salasana';
$lang['BT_ADD_AUTH_KEY'] = 'Mahdollistaa lisäämällä salasana torrent-tiedostoja ennen lataamista';

$lang['BT_TOR_BROWSE_ONLY_REG_HEAD'] = 'Torrent-selain (tracker)';
$lang['BT_TOR_BROWSE_ONLY_REG'] = 'Torrent-selain (tracker.php) pääsee vain kirjautuneet käyttäjät';
$lang['BT_SEARCH_BOOL_MODE'] = 'Anna boolean full-text-haut';
$lang['BT_SEARCH_BOOL_MODE_EXPL'] = 'käyttö *, +, -,.. haut';

$lang['BT_SHOW_DL_STAT_ON_INDEX_HEAD'] = "Sekalainen";
$lang['BT_SHOW_DL_STAT_ON_INDEX'] = "Näyttää käyttäjät, UL/DL tilastot yläreunassa foorumin pääsivulla";
$lang['BT_NEWTOPIC_AUTO_REG'] = 'Automaattisesti rekisteröityä torrent tracker uusia aiheita';
$lang['BT_SET_DLTYPE_ON_TOR_REG'] = 'Muuttaa aihe-tila "Lataa", kun rekisteröitymättä torrent tracker';
$lang['BT_SET_DLTYPE_ON_TOR_REG_EXPL'] = 'muuttaa aihe tyyppi "Lataa" - riippumatta siitä, foorumin asetukset';
$lang['BT_UNSET_DLTYPE_ON_TOR_UNREG'] = 'Muuttaa aihe-tila "Normaali", kun taas kirjaamaton torrent tracker';

// Release
$lang['TEMPLATE_DISABLE'] = 'Mallin käytöstä';
$lang['FOR_NEW_TEMPLATE'] = 'uusia kuvioita!';
$lang['CHANGED'] = 'Muuttunut';
$lang['REMOVED'] = 'Poistettu';
$lang['QUESTION'] = 'Confirm are you sure you want to perform this action';

$lang['CRON_LIST'] = 'Cron lista';
$lang['CRON_ID'] = 'ID';
$lang['CRON_ACTIVE'] = 'Päälle';
$lang['CRON_ACTIVE_EXPL'] = 'Aktiiviset tehtävät';
$lang['CRON_TITLE'] = 'Otsikko';
$lang['CRON_SCRIPT'] = 'Script';
$lang['CRON_SCHEDULE'] = 'Aikataulu';
$lang['CRON_LAST_RUN'] = 'Viime Ajaa';
$lang['CRON_NEXT_RUN'] = 'Seuraava Ajaa';
$lang['CRON_RUN_COUNT'] = 'Toimii';
$lang['CRON_MANAGE'] = 'Hallita';
$lang['CRON_OPTIONS'] = 'Cron vaihtoehtoja';
$lang['CRON_DISABLED_WARNING'] = 'Varoitus! cron-skriptien suorittaminen on poistettu käytöstä. Ota se käyttöön asettamalla muuttuja APP_CRON_ENABLED.';

$lang['CRON_ENABLED'] = 'Cron käytössä';
$lang['CRON_CHECK_INTERVAL'] = 'Cron-tarkista väli (sec)';

$lang['WITH_SELECTED'] = 'Valittujen';
$lang['NOTHING'] = 'tehdä mitään';
$lang['CRON_RUN'] = 'Ajaa';
$lang['CRON_DEL'] = 'Poista';
$lang['CRON_DISABLE'] = 'Poistaa';
$lang['CRON_ENABLE'] = 'Jotta';

$lang['RUN_MAIN_CRON'] = 'Aloittaa cron';
$lang['ADD_JOB'] = 'Lisää cron job';
$lang['DELETE_JOB'] = 'Oletko varma, että haluat poistaa ajastettu tehtävä?';
$lang['CRON_WORKS'] = 'Cron on nyt toimii tai on rikki -> ';
$lang['REPAIR_CRON'] = 'Korjaus Cron';

$lang['CRON_EDIT_HEAD_EDIT'] = 'Muokkaa työtä';
$lang['CRON_EDIT_HEAD_ADD'] = 'Lisää työtä';
$lang['CRON_SCRIPT_EXPL'] = 'nimen kirjoitus "sisältää/cron/työpaikat/"';
$lang['SCHEDULE'] = [
    'select' => '&raquo; Valitse käynnistä',
    'hourly' => 'tunneittain',
    'daily' => 'päivittäin',
    'weekly' => 'viikoittain',
    'monthly' => 'kuukausittain',
    'interval' => 'väli'
];
$lang['NOSELECT'] = 'Ei valitse';
$lang['RUN_DAY'] = 'Ajaa päivä';
$lang['RUN_DAY_EXPL'] = 'päivä, jolloin tämä työ ajaa';
$lang['RUN_TIME'] = 'Run aika';
$lang['RUN_TIME_EXPL'] = 'kun tätä työtä suorittaa (esim. 05:00:00)';
$lang['RUN_ORDER'] = 'Jotta ajaa';
$lang['LAST_RUN'] = 'Viime Ajaa';
$lang['NEXT_RUN'] = 'Seuraava Ajaa';
$lang['RUN_INTERVAL'] = 'Suorita väli';
$lang['RUN_INTERVAL_EXPL'] = 'esim 00:10:00';
$lang['LOG_ENABLED'] = 'Log käytössä';
$lang['LOG_FILE'] = 'Lokitiedosto';
$lang['LOG_FILE_EXPL'] = 'tiedosto tallenna lokitiedot';
$lang['LOG_SQL_QUERIES'] = 'Kirjautua SQL-kyselyjä';
$lang['FORUM_DISABLE'] = 'Poistaa aluksella';
$lang['BOARD_DISABLE_EXPL'] = 'poistaa aluksella, kun tämä työ on ajaa';
$lang['RUN_COUNTER'] = 'Vastoin';

$lang['JOB_REMOVED'] = 'Ongelma on poistettu onnistuneesti';
$lang['SCRIPT_DUPLICATE'] = 'Käsikirjoitus <b>' . @$_POST['cron_script'] . '</b> on jo olemassa!';
$lang['TITLE_DUPLICATE'] = 'Tehtävän Nimi <b>' . @$_POST['cron_title'] . '</b> on jo olemassa!';
$lang['CLICK_RETURN_JOBS_ADDED'] = '%sReturn lisäksi problem%s';
$lang['CLICK_RETURN_JOBS'] = '%sBack Tehtävään Scheduler%s';

$lang['REBUILD_SEARCH'] = 'Rakentaa Haku Indeksi';
$lang['REBUILD_SEARCH_DESC'] = 'Tämä mod on indeksi joka lähettää your forum, uudelleenrakentaminen haku taulukoita. Voit lopettaa milloin haluat ja seuraavan kerran käynnistät sen uudelleen, voit jatkaa siitä mihin jäit.<br /><br />It voi kestää kauan näyttää sen edistymistä (riippuen "Viestiä per sykli" ja "määräaika"), joten älä siirrä sen edistymistä sivulla, kunnes se on täydellinen, ellei tietysti haluat keskeyttää se.';

// Input screen
$lang['STARTING_POST_ID'] = 'Alkaa post_id';
$lang['STARTING_POST_ID_EXPLAIN'] = 'Ensimmäinen viesti, jossa käsittely alkaa from<br />You voi halutessaan aloittaa alusta tai post olet viimeksi pysähtynyt';

$lang['START_OPTION_BEGINNING'] = 'aloittaa alusta';
$lang['START_OPTION_CONTINUE'] = 'jatkaa viimeksi pysähtyi';

$lang['CLEAR_SEARCH_TABLES'] = 'Tyhjennä haku taulukot';
$lang['CLEAR_SEARCH_TABLES_EXPLAIN'] = '';
$lang['CLEAR_SEARCH_NO'] = 'EI';
$lang['CLEAR_SEARCH_DELETE'] = 'POISTA';
$lang['CLEAR_SEARCH_TRUNCATE'] = 'TRUNCATE';

$lang['NUM_OF_POSTS'] = 'Virkojen määrä';
$lang['NUM_OF_POSTS_EXPLAIN'] = 'Määrä yhteensä virkaa process<br />It on automaattisesti täynnä lukumäärä yhteensä/jäljellä olevat viestit löydy db';

$lang['POSTS_PER_CYCLE'] = 'Viestiä per sykli';
$lang['POSTS_PER_CYCLE_EXPLAIN'] = 'Virkoja prosessi per cycle<br />Keep se alhainen välttää php/webserver aikakatkaisut';

$lang['REFRESH_RATE'] = 'Virkistystaajuus';
$lang['REFRESH_RATE_EXPLAIN'] = 'Kuinka paljon aikaa (sekuntia) pysyä tyhjäkäynnillä ennen siirtymistä seuraavaan käsittelyyn cycle<br />Usually sinun ei tarvitse muuttaa tätä';

$lang['TIME_LIMIT'] = 'Määräaika';
$lang['TIME_LIMIT_EXPLAIN'] = 'Kuinka paljon aikaa (sekuntia) viesti käsittely voi kestää, ennen kuin siirrytään seuraavan jakson';
$lang['TIME_LIMIT_EXPLAIN_SAFE'] = '<i>Your php (safe mode) on timeout %s sekuntia määritetty, joten pysyä alle tämän value</i>';
$lang['TIME_LIMIT_EXPLAIN_WEBSERVER'] = '<i>Your webserver on timeout %s sekuntia määritetty, joten pysyä alle tämän value</i>';

$lang['DISABLE_BOARD'] = 'Poistaa aluksella';
$lang['DISABLE_BOARD_EXPLAIN'] = 'Onko vai ei poistaa teidän aluksella, kun käsittely';
$lang['DISABLE_BOARD_EXPLAIN_ENABLED'] = 'Se tulee käyttöön automaattisesti, kun loppuun käsittely';
$lang['DISABLE_BOARD_EXPLAIN_ALREADY'] = '<i>Your hallitus on jo disabled</i>';

// Information strings
$lang['INFO_PROCESSING_STOPPED'] = 'Viimeksi pysähtyi käsittelyn post_id %s (%s käsitelty virkaa) %s';
$lang['INFO_PROCESSING_ABORTED'] = 'Viimeksi keskeyttänyt käsittelyn post_id %s (%s käsitelty virkaa) %s';
$lang['INFO_PROCESSING_ABORTED_SOON'] = 'Odota muutama minuuttia ennen kuin jatkat...';
$lang['INFO_PROCESSING_FINISHED'] = 'Olet onnistuneesti päättynyt käsittely (%s käsitelty virkaa) %s';
$lang['INFO_PROCESSING_FINISHED_NEW'] = 'Olet onnistuneesti päättynyt käsittely post_id %s (%s käsitelty virkaa) %s,<br />but on ollut %s uusi viesti(t) kyseisen päivämäärän jälkeen';

// Progress screen
$lang['REBUILD_SEARCH_PROGRESS'] = 'Rakentaa Etsimään Edistystä';

$lang['PROCESSED_POST_IDS'] = 'Käsitelty post tunnukset : %s - %s';
$lang['TIMER_EXPIRED'] = 'Ajastin on päättynyt %s sekuntia. ';
$lang['CLEARED_SEARCH_TABLES'] = 'Selvitetty haku taulukoita. ';
$lang['DELETED_POSTS'] = '%s post(s) oli poistettu käyttäjät käsittelyn aikana. ';
$lang['PROCESSING_NEXT_POSTS'] = 'Käsittely ensi %s post(s). Ole hyvä ja odota...';
$lang['ALL_SESSION_POSTS_PROCESSED'] = 'Käsitellä kaikki viestit nykyisen istunnon.';
$lang['ALL_POSTS_PROCESSED'] = 'Kaikki viestit on käsitelty onnistuneesti.';
$lang['ALL_TABLES_OPTIMIZED'] = 'Etsi kaikki pöydät olivat optimoitu onnistuneesti.';

$lang['PROCESSING_POST_DETAILS'] = 'Käsittely post';
$lang['PROCESSED_POSTS'] = 'Käsitellyt Viestit';
$lang['PERCENT'] = 'Prosenttia';
$lang['CURRENT_SESSION'] = 'Nykyisen Istunnon';
$lang['TOTAL'] = 'Yhteensä';

$lang['PROCESS_DETAILS'] = 'alkaen <b>%s</b> että <b>%s</b> (yhteensä <b>%s</b>)';
$lang['PERCENT_COMPLETED'] = '%s %% valmistunut';

$lang['PROCESSING_TIME_DETAILS'] = 'Nykyisen istunnon tiedot';
$lang['PROCESSING_TIME'] = 'Käsittelyaika';
$lang['TIME_LAST_POSTS'] = 'Viime %s post(s)';
$lang['TIME_FROM_THE_BEGINNING'] = 'Alusta';
$lang['TIME_AVERAGE'] = 'Keskimäärin per jakso';
$lang['TIME_ESTIMATED'] = 'Arvioitu, kunnes loppuun';

$lang['DATABASE_SIZE_DETAILS'] = 'Tietokannan koko yksityiskohtia';
$lang['SIZE_CURRENT'] = 'Nykyinen';
$lang['SIZE_ESTIMATED'] = 'Arvioitu jälkeen viimeistely';
$lang['SIZE_SEARCH_TABLES'] = 'Etsi Taulukot koko';
$lang['SIZE_DATABASE'] = 'Tietokannan koko';

$lang['ACTIVE_PARAMETERS'] = 'Aktiivinen parametrit';
$lang['POSTS_LAST_CYCLE'] = 'Käsitelty post(s) viimeinen sykli';
$lang['BOARD_STATUS'] = 'Hallituksen asema';

$lang['INFO_ESTIMATED_VALUES'] = '(*) Kaikki arvioidut arvot lasketaan approximately<br />based nykyisen valmistunut prosenttia ja voi edustaa todellinen lopulliset arvot.<br />As valmistunut prosenttia lisää arvioidut arvot tulevat lähemmäksi todellinen itse.';

$lang['CLICK_RETURN_REBUILD_SEARCH'] = 'Klikkaa %shere%s palata uudelleen Haku';
$lang['REBUILD_SEARCH_ABORTED'] = 'Rakentaa haku keskeytettiin post_id %s.<br /><br />If olet keskeyttänyt käsittelyn aikana, sinun täytyy odottaa jonkin minuutin, kunnes suoritat Rakentaa Haku uudelleen, joten viimeinen sykli voi lopettaa.';
$lang['WRONG_INPUT'] = 'Olet antanut joitakin vääriä arvoja. Tarkista input ja yritä uudelleen.';

// Buttons
$lang['PROCESSING'] = 'Käsittely...';
$lang['FINISHED'] = 'Valmis';

$lang['BOT_TOPIC_MOVED_FROM_TO'] = 'Topic has been moved from forum [b]%s[/b] to forum [b]%s[/b].[br][b]Reason to move:[/b] %s[br][br]%s';
$lang['BOT_MESS_SPLITS'] = 'Aihe on jaettu kahteen osaan. Uusi aihe - [b]%s[/b][br][br]%s';
$lang['BOT_TOPIC_SPLITS'] = 'Aihe on erotettu [b]%s[/b][br][br]%s';

$lang['CALLSEED'] = 'Call seeds';
$lang['CALLSEED_EXPLAIN'] = 'Ota ilmoitus pyynnön palata jakelu';
$lang['CALLSEED_SUBJECT'] = 'Lataa auttaa %s';
$lang['CALLSEED_TEXT'] = 'Hei![br]Your apua tarvitaan, vapauttaa [url=%s]%s[/url][br]if päätät auttaa, mutta jo poistaa torrent-tiedoston, voit ladata sen [url=%s]this[/url][br][br]i toivoa apuasi!';
$lang['CALLSEED_MSG_OK'] = 'Viesti on lähetetty kaikille niille, jotka ladata tämä julkaisu';
$lang['CALLSEED_MSG_SPAM'] = 'Pyyntö on jo kerran lähetetty onnistuneesti (Luultavasti ei)<br /><br />The seuraava tilaisuus lähettää pyynnön olla <b>%s</b>.';
$lang['CALLSEED_HAVE_SEED'] = 'Aihe ei vaadi apua (<b>Seeders:</b> %d)';

$lang['LOG_ACTION']['LOG_TYPE'] = [
    'mod_topic_delete' => 'Aihe:<br /> <b>deleted</b>',
    'mod_topic_move' => 'Aihe:<br /> <b>moved</b>',
    'mod_topic_lock' => 'Aihe:<br /> <b>closed</b>',
    'mod_topic_unlock' => 'Aihe:<br /> <b>opened</b>',
    'mod_topic_split' => 'Aihe:<br /> <b>split</b>',
    'mod_topic_set_downloaded' => 'Topic:<br /> <b>set downloaded</b>',
    'mod_topic_unset_downloaded' => 'Topic:<br /> <b>unset downloaded</b>',
    'mod_topic_change_tor_status' => 'Topic:<br /> <b>changed torrent status</b>',
    'mod_topic_change_tor_type' => 'Topic:<br /> <b>changed torrent type</b>',
    'mod_topic_tor_unregister' => 'Topic:<br /> <b>torrent unregistered</b>',
    'mod_topic_tor_register' => 'Topic:<br /> <b>torrent registered</b>',
    'mod_topic_tor_delete' => 'Topic:<br /> <b>torrent deleted</b>',
    'mod_topic_renamed' => 'Topic:<br /> <b>renamed</b>',
    'mod_post_delete' => 'Viesti:<br /> <b>deleted</b>',
    'mod_post_pin' => 'Post:<br /> <b>pinned</b>',
    'mod_post_unpin' => 'Post:<br /> <b>unpinned</b>',
    'adm_user_delete' => 'Käyttäjä:<br /> <b>deleted</b>',
    'adm_user_ban' => 'Käyttäjä:<br /> <b>ban</b>',
    'adm_user_unban' => 'Käyttäjä:<br /> <b>unban</b>',
];

$lang['ACTS_LOG_ALL_ACTIONS'] = 'Kaikki toimet';
$lang['ACTS_LOG_SEARCH_OPTIONS'] = 'Toimet Log: Etsi vaihtoehtoja';
$lang['ACTS_LOG_FORUM'] = 'Forum';
$lang['ACTS_LOG_ACTION'] = 'Toiminta';
$lang['ACTS_LOG_USER'] = 'Käyttäjä';
$lang['ACTS_LOG_LOGS_FROM'] = 'Lokit ';
$lang['ACTS_LOG_FIRST'] = 'alkaen';
$lang['ACTS_LOG_DAYS_BACK'] = 'päivää takaisin';
$lang['ACTS_LOG_TOPIC_MATCH'] = 'Aiheen otsikko ottelu';
$lang['ACTS_LOG_SORT_BY'] = 'Lajittele';
$lang['ACTS_LOG_LOGS_ACTION'] = 'Toiminta';
$lang['ACTS_LOG_USERNAME'] = 'Käyttäjätunnus';
$lang['ACTS_LOG_TIME'] = 'Aika';
$lang['ACTS_LOG_INFO'] = 'Info';
$lang['ACTS_LOG_FILTER'] = 'Suodatin';
$lang['ACTS_LOG_TOPICS'] = 'Aiheet:';
$lang['ACTS_LOG_OR'] = 'tai';

$lang['RELEASE'] = 'Julkaisu Malleja';
$lang['RELEASES'] = 'Tiedotteet';

$lang['BACK'] = 'Takaisin';
$lang['ERROR_FORM'] = 'Virheellinen kentät';
$lang['RELEASE_WELCOME'] = 'Ole hyvä ja täytä vapautuslomake';
$lang['NEW_RELEASE'] = 'Uusi julkaisu';
$lang['NEXT'] = 'Edelleen';
$lang['OTHER'] = 'Muut';
$lang['OTHERS'] = 'Others';
$lang['ALL'] = 'All';

$lang['TPL_EMPTY_FIELD'] = 'Sinun täytyy täyttää kentän <b>%s</b>';
$lang['TPL_EMPTY_SEL'] = 'Sinun täytyy valita <b>%s</b>';
$lang['TPL_NOT_NUM'] = '<b>%s</b> - Ei num';
$lang['TPL_NOT_URL'] = '<b>%s</b> - Täytyy olla https:// URL-osoite';
$lang['TPL_NOT_IMG_URL'] = '<b>%s</b> - Täytyy olla https:// IMG_URL';
$lang['TPL_PUT_INTO_SUBJECT'] = 'otetaan aihe';
$lang['TPL_POSTER'] = 'juliste';
$lang['TPL_REQ_FILLING'] = 'vaatii täyttö';
$lang['TPL_NEW_LINE'] = 'uusi linja';
$lang['TPL_NEW_LINE_AFTER'] = 'uusi rivi otsikon jälkeen';
$lang['TPL_NUM'] = 'numero';
$lang['TPL_URL'] = 'URL';
$lang['TPL_IMG'] = 'kuva';
$lang['TPL_PRE'] = 'pre';
$lang['TPL_SPOILER'] = 'spoileri';
$lang['TPL_IN_LINE'] = 'samalla linjalla';
$lang['TPL_HEADER_ONLY'] = 'vain otsikko';

$lang['SEARCH_INVALID_USERNAME'] = 'Virheellinen käyttäjänimi syötetty hakuun';
$lang['SEARCH_INVALID_EMAIL'] = 'Virheellinen sähköpostiosoite hakua varten';
$lang['SEARCH_INVALID_IP'] = 'Virheellinen IP-osoite hakua varten';
$lang['SEARCH_INVALID_GROUP'] = 'Virheellinen ryhmä syötetty hakuun';
$lang['SEARCH_INVALID_RANK'] = 'Virheellinen sijoitus hakuun';
$lang['SEARCH_INVALID_DATE'] = 'Virheellinen hakupäivämäärä';
$lang['SEARCH_INVALID_POSTCOUNT'] = 'Hakuun syötetty virheellinen viestimäärä';
$lang['SEARCH_INVALID_USERFIELD'] = 'Virheellinen Userfield syötetyt tiedot';
$lang['SEARCH_INVALID_LASTVISITED'] = 'Virheellinen päivämäärä viimeisimmän vierailun hakuun';
$lang['SEARCH_INVALID_LANGUAGE'] = 'Virheellinen Valittu Kieli';
$lang['SEARCH_INVALID_TIMEZONE'] = 'Virheellinen Aikavyöhyke Valittu';
$lang['SEARCH_INVALID_MODERATORS'] = 'Virheellinen Forum Valittu';
$lang['SEARCH_INVALID'] = 'Virheellinen Haku';
$lang['SEARCH_INVALID_DAY'] = 'Päivä tuli oli virheellinen';
$lang['SEARCH_INVALID_MONTH'] = 'Kuukausi tuli oli virheellinen';
$lang['SEARCH_INVALID_YEAR'] = 'Vuoden syötit oli virheellinen';
$lang['SEARCH_FOR_USERNAME'] = 'Haku käyttäjätunnuksia matching %s';
$lang['SEARCH_FOR_EMAIL'] = 'Haku sähköpostiosoitteet matching %s';
$lang['SEARCH_FOR_IP'] = 'Etsimällä IP-osoitteita vastaavat %s';
$lang['SEARCH_FOR_DATE'] = 'Haku-käyttäjät, jotka liittyivät %s %d/%d/%d';
$lang['SEARCH_FOR_GROUP'] = 'Etsimällä ryhmän jäseniä %s';
$lang['SEARCH_FOR_RANK'] = 'Haku lentoyhtiöiden listalla %s';
$lang['SEARCH_FOR_BANNED'] = 'Haku kiellettyjen käyttäjien';
$lang['SEARCH_FOR_ADMINS'] = 'Haku Ylläpitäjät';
$lang['SEARCH_FOR_MODS'] = 'Haku Moderaattorit';
$lang['SEARCH_FOR_DISABLED'] = 'Haku vammaisia käyttäjiä varten';
$lang['SEARCH_FOR_POSTCOUNT_GREATER'] = 'Etsivät käyttäjät, joilla on post count suurempi kuin %d';
$lang['SEARCH_FOR_POSTCOUNT_LESSER'] = 'Etsivät käyttäjät, joilla on post count vähemmän kuin %d';
$lang['SEARCH_FOR_POSTCOUNT_RANGE'] = 'Etsivät käyttäjät, joilla on post count välillä %d ja %d';
$lang['SEARCH_FOR_POSTCOUNT_EQUALS'] = 'Etsivät käyttäjät, joilla on post count-arvo %d';
$lang['SEARCH_FOR_USERFIELD_ICQ'] = 'Etsiminen käyttäjien kanssa ICQ osoite matching %s';
$lang['SEARCH_FOR_USERFIELD_SKYPE'] = 'Etsivät käyttäjät, joilla on Skype-matching %s';
$lang['SEARCH_FOR_USERFIELD_TWITTER'] = 'Etsivät käyttäjät, joilla on Twitter-matching %s';
$lang['SEARCH_FOR_USERFIELD_WEBSITE'] = 'Etsivät käyttäjät, joilla on Sivuston matching %s';
$lang['SEARCH_FOR_USERFIELD_LOCATION'] = 'Etsimällä käyttäjille Sijainti matching %s';
$lang['SEARCH_FOR_USERFIELD_INTERESTS'] = 'Etsiminen käyttäjien kanssa heidän Etujaan alalla matching %s';
$lang['SEARCH_FOR_USERFIELD_OCCUPATION'] = 'Etsiä käyttäjiä heidän Ammatti-kentän vastaavia %s';
$lang['SEARCH_FOR_LASTVISITED_INTHELAST'] = 'Searching for users who have visited in the last %s';
$lang['SEARCH_FOR_LASTVISITED_AFTERTHELAST'] = 'Searching for users who have visited after the last %s';
$lang['SEARCH_FOR_LANGUAGE'] = 'Etsivät käyttäjät, jotka ovat asettaneet %s, koska niiden kieli';
$lang['SEARCH_FOR_TIMEZONE'] = 'Etsivät käyttäjät, jotka on asetettu UTC %s kuin heidän aikavyöhyke';
$lang['SEARCH_FOR_STYLE'] = 'Etsivät käyttäjät, jotka ovat asettaneet %s koska niiden tyyli';
$lang['SEARCH_FOR_MODERATORS'] = 'Etsi valvojat foorumi -> %s';
$lang['SEARCH_USERS_ADVANCED'] = 'Tarkennettu Haku';
$lang['SEARCH_USERS_EXPLAIN'] = 'Tämän Moduulin avulla voit suorittaa tarkennettuja hakuja käyttäjille monenlaisia kriteerejä. Lue kuvaukset kunkin alan ymmärtää jokainen haku vaihtoehto kokonaan.';
$lang['SEARCH_USERNAME_EXPLAIN'] = 'Täällä voit suorittaa kirjainkoko etsi käyttäjätunnukset. Jos haluat ottelu osa käyttäjätunnus, käytä * (asterix) jokerina.';
$lang['SEARCH_EMAIL_EXPLAIN'] = 'Kirjoita lauseke, joka vastaa käyttäjän sähköpostiosoite. Tämä on kirjainkoko. Jos haluat tehdä osittainen vastaavuus, käytä * (asterix) jokerina.';
$lang['SEARCH_IP_EXPLAIN'] = 'Etsi käyttäjien tietyn IP-osoite (xxx.xxx.xxx.xxx).';
$lang['SEARCH_USERS_JOINED'] = 'Käyttäjät, jotka liittyivät';
$lang['SEARCH_USERS_LASTVISITED'] = 'Käyttäjät, joille on käynyt';
$lang['IN_THE_LAST'] = 'viime';
$lang['AFTER_THE_LAST'] = 'sen jälkeen, kun viimeinen';
$lang['BEFORE'] = 'Ennen';
$lang['AFTER'] = 'Jälkeen';
$lang['SEARCH_USERS_JOINED_EXPLAIN'] = 'Etsi käyttäjille liittyä Ennen tai sen Jälkeen (ja edelleen) tiettyyn päivämäärään mennessä. Päivämäärän muoto on VVVV/KK/PP.';
$lang['SEARCH_USERS_GROUPS_EXPLAIN'] = 'Näytä kaikki jäsenet on valittu ryhmään.';
$lang['SEARCH_USERS_RANKS_EXPLAIN'] = 'Näytä kaikki kantajia valittujen listalla.';
$lang['BANNED_USERS'] = 'Kiellettyjen Käyttäjien';
$lang['DISABLED_USERS'] = 'Vammaisten Käyttäjien';
$lang['SEARCH_USERS_MISC_EXPLAIN'] = 'Järjestelmänvalvojat - Kaikki käyttäjät, joilla on järjestelmänvalvojan oikeudet; Moderaattorit - Kaikki foorumin moderaattorit; Kielletyt käyttäjät - kaikki tilit, jotka on estetty näillä foorumeilla; Vammaiset käyttäjät - Kaikki käyttäjät, joilla on pois käytöstä tilit (joko poistettu käytöstä manuaalisesti tai eivät koskaan vahvistaneet sähköpostiosoitettaan); Käyttäjät, joiden PM:t ovat pois käytöstä – Valitsee käyttäjät, joilta on poistettu yksityisviestien käyttöoikeudet (Tehty käyttäjähallinnan kautta)';
$lang['POSTCOUNT'] = 'Viestien määrä';
$lang['EQUALS'] = 'Vastaa';
$lang['GREATER_THAN'] = 'Suurempi kuin';
$lang['LESS_THAN'] = 'Alle';
$lang['SEARCH_USERS_POSTCOUNT_EXPLAIN'] = 'Voit etsiä käyttäjiä viestimäärän arvon perusteella. Voit etsiä joko tietyn arvon perusteella, joka on suurempi tai pienempi kuin arvo tai kahden arvon välillä. Suorittaaksesi alueen haun, valitse "Yhtä kuin" ja kirjoita alueen alku- ja loppuarvot erotettuina väliviivalla (-), esim. 10-15';
$lang['USERFIELD'] = 'Userfield';
$lang['SEARCH_USERS_USERFIELD_EXPLAIN'] = 'Hae käyttäjille perustuu eri profiilin kentät. Jäkerimerkit tuetaan käyttämällä an asterix (*).';
$lang['SEARCH_USERS_LASTVISITED_EXPLAIN'] = 'Voit etsiä käyttäjiä jotka perustuvat niiden viimeksi kirjautuminen päivämäärä käyttämällä tämä haku vaihtoehto';
$lang['SEARCH_USERS_LANGUAGE_EXPLAIN'] = 'Tämä näyttää käyttäjät, joille on valittu tiettyä kieltä niiden Profiilia';
$lang['SEARCH_USERS_TIMEZONE_EXPLAIN'] = 'Käyttäjät, jotka ovat valinneet tietyn aikavyöhykkeen niiden profiilia';
$lang['SEARCH_USERS_STYLE_EXPLAIN'] = 'Näyttää käyttäjät, jotka on valittu tietyn tyylin.';
$lang['MODERATORS_OF'] = 'Valvojat';
$lang['SEARCH_USERS_MODERATORS_EXPLAIN'] = 'Etsi käyttäjiä, joilla on tietyn keskusteluryhmän valvontaoikeudet. Moderointioikeudet tunnistetaan joko käyttäjien oikeuksista tai ryhmässä olemisesta, jolla on oikeat ryhmäoikeudet.';

$lang['SEARCH_USERS_NEW'] = '%s tuotti %d tulos(s). Suorittaa <a href="%s">another search</a>.';
$lang['BANNED'] = 'Kielletty';
$lang['NOT_BANNED'] = 'Ei Ole Kielletty';
$lang['SEARCH_NO_RESULTS'] = 'Ei-käyttäjät vastaavat valitsemasi kriteerit. Kokeile uuden haun. Jos olet etsimässä käyttäjätunnus tai sähköpostiosoite kentät, osittainen ottelut sinun täytyy käyttää yleismerkkiä * (asterix).';
$lang['ACCOUNT_STATUS'] = 'Tilin Tila';
$lang['SORT_OPTIONS'] = 'Lajitella vaihtoehtoja:';
$lang['LAST_VISIT'] = 'Viimeinen Vierailu';
$lang['DAY'] = 'Päivä';

$lang['POST_EDIT_CANNOT'] = 'Pahoillani, mutta et voi muokata viestejäsi';
$lang['FORUMS_IN_CAT'] = 'foorumeilla tähän luokkaan';

$lang['MC_TITLE'] = 'Kommentti Maltillisuus';
$lang['MC_LEGEND'] = 'Kirjoita kommentti';
$lang['MC_FAQ'] = 'Syötetty teksti näytetään alla tämä viesti';
$lang['MC_COMMENT_PM_SUBJECT'] = "%s viestisi";
$lang['MC_COMMENT_PM_MSG'] = "Hei, [b]%s[/b]\nModerator jättää viestisi [url=%s][b]%s[/b][/url][quote]\n%s\n[/quote]";
$lang['MC_COMMENT'] = [
    0 => [
        'title' => '',
        'type' => 'Poista kommentti',
    ],
    1 => [
        'title' => 'Kommentti %s',
        'type' => 'Kommentti',
    ],
    2 => [
        'title' => 'Tietoja %s',
        'type' => 'Tiedot',
    ],
    3 => [
        'title' => 'Varoitus %s',
        'type' => 'Varoitus',
    ],
    4 => [
        'title' => 'Rikkomuksesta %s',
        'type' => 'Vastoin',
    ],
];

$lang['SITEMAP'] = 'Sivukartta';
$lang['SITEMAP_ADMIN'] = 'Hallita sivukartta';
$lang['SITEMAP_CREATED'] = 'Sivukartta on luotu';
$lang['SITEMAP_AVAILABLE'] = 'ja on saatavilla osoitteessa';
$lang['SITEMAP_NOT_CREATED'] = 'Sivukartta ei ole vielä luotu';
$lang['SITEMAP_OPTIONS'] = 'Vaihtoehtoja';
$lang['SITEMAP_CREATE'] = 'Luo / päivittää sivukartta';
$lang['SITEMAP_WHAT_NEXT'] = 'Mitä tehdä seuraavaksi?';
$lang['SITEMAP_GOOGLE_1'] = 'Rekisteröidy sivuston milloin <a href="https://www.google.com/webmasters/" target="_blank">Google Webmaster</a> Google-tilisi avulla.';
$lang['SITEMAP_GOOGLE_2'] = '<a href="https://www.google.com/webmasters/tools/sitemap-list" target="_blank">Add sitemap</a> sivuston olet rekisteröitynyt.';
$lang['SITEMAP_YANDEX_1'] = 'Rekisteröidy sivuston milloin <a href="https://webmaster.yandex.ru/sites/" target="_blank">Yandex Webmaster</a> käyttämällä Yandex-tilin.';
$lang['SITEMAP_YANDEX_2'] = '<a href="https://webmaster.yandex.ru/site/map.xml" target="_blank">Add sitemap</a> sivuston olet rekisteröitynyt.';
$lang['SITEMAP_BING_1'] = 'Rekisteröidy sivuston milloin <a href="https://www.bing.com/webmaster/" target="_blank">Bing Webmaster</a> käyttämällä Microsoft-tilisi.';
$lang['SITEMAP_BING_2'] = 'Lisää sivukartta sivuston olet rekisteröitynyt sen asetukset.';
$lang['SITEMAP_ADD_TITLE'] = 'Lisää sivuja sivukartta';
$lang['SITEMAP_ADD_PAGE'] = 'Lisää sivuja';
$lang['SITEMAP_ADD_EXP_1'] = 'Voit määrittää muita sivuja sivustosi, joka olisi sisällytettävä teidän sivustokarttatiedoston, että olet luomassa.';
$lang['SITEMAP_ADD_EXP_2'] = 'Jokainen viittaus täytyy alkaa http(s):// ja uusi linja!';

$lang['FORUM_MAP'] = 'Foorumeita kartta';
$lang['ATOM_FEED'] = 'Feed';
$lang['ATOM_ERROR'] = 'Virhe tuottaa rehun';
$lang['ATOM_SUBSCRIBE'] = 'Tilaa syöte';
$lang['ATOM_NO_MODE'] = 'No mode option provided for the feed';
$lang['ATOM_NO_FORUM'] = 'Tämä foorumi ei ole ruokkia (ei käynnissä olevia aiheita)';
$lang['ATOM_NO_USER'] = 'Tämä käyttäjä ei ole ruokkia (ei käynnissä olevia aiheita)';
$lang['ATOM_UPDATED'] = 'Päivitetty';
$lang['ATOM_GLOBAL_FEED'] = 'Maailmanlaajuinen rehu kaikille foorumeille';

$lang['HASH_INVALID'] = 'Hash %s on virheellinen';
$lang['HASH_NOT_FOUND'] = 'Julkaisu hash %s ei löytynyt';

$lang['TERMS_EMPTY_TEXT'] = '[align=center]The text of this page is edited at: [url]%s[/url]. This line can see only administrators.[/align]';
$lang['TERMS_EXPLAIN'] = 'Tällä sivulla, voit määrittää tekstin perussäännöt resurssi näkyy käyttäjille.';
$lang['TERMS_UPDATED_SUCCESSFULLY'] = 'Terms have been updated successfully';
$lang['CLICK_RETURN_TERMS_CONFIG'] = '%sClick Here to return to Terms editor%s';

$lang['TR_STATS'] = [
    0 => 'aktiivisia käyttäjiä 30 päivää',
    1 => 'aktiivisia käyttäjiä 90 päivää',
    2 => 'medium size distributions on the tracker',
    3 => 'kuinka monta yhteensä kädet tracker',
    4 => 'kuinka monet elävät kädet (siellä on ainakin 1 led)',
    5 => 'kuinka monta kättä missä se kylvö yli 5 siemenet',
    6 => 'kuinka moni meistä lähettäjät (ne, jotka täyttivät vähintään 1 käsi)',
    7 => 'kuinka monta lähettäjät viimeisten 30 päivää',
];

$lang['NEW_POLL_START'] = 'Kysely käytössä';
$lang['NEW_POLL_END'] = 'Kysely täytetty';
$lang['NEW_POLL_ENDED'] = 'Tämä kysely on jo saatu päätökseen';
$lang['NEW_POLL_DELETE'] = 'Kysely poistetaan';
$lang['NEW_POLL_ADDED'] = 'Kysely lisätty';
$lang['NEW_POLL_ALREADY'] = 'Teema on jo poll';
$lang['NEW_POLL_RESULTS'] = 'Kyselyn muuttunut ja vanhat tulokset poistettu';
$lang['NEW_POLL_VOTES'] = 'Sinun täytyy syöttää oikea vastaus vaihtoehtoja (minimi 2, maksimi on %s)';
$lang['NEW_POLL_DAYS'] = 'Aikaa kyselyn (%s päivää siitä hetkestä luomisen teema) jo päättynyt';
$lang['NEW_POLL_U_NOSEL'] = 'Et ole valinnut, että äänestys';
$lang['NEW_POLL_U_CHANGE'] = 'Muokkaa kyselyn';
$lang['NEW_POLL_U_EDIT'] = 'Muuttaa kyselyn (vanha tulokset poistetaan)';
$lang['NEW_POLL_U_VOTED'] = 'Kaikki äänestäneet';
$lang['NEW_POLL_U_START'] = 'Jotta kysely';
$lang['NEW_POLL_U_END'] = 'Valmis kysely';
$lang['NEW_POLL_M_TITLE'] = 'Otsikko kysely';
$lang['NEW_POLL_M_VOTES'] = 'Vaihtoehtoja';
$lang['NEW_POLL_M_EXPLAIN'] = 'Kukin rivi vastaa yksi vastaus (max';

$lang['OLD_BROWSER'] = 'Käytät vanhentunutta selainta. Sivusto ei näy oikein.';
$lang['GO_BACK'] = 'Mene takaisin';

$lang['UPLOAD_ERROR_COMMON_DISABLED'] = 'File upload disabled';
$lang['UPLOAD_ERROR_COMMON'] = 'Tiedoston siirto palvelimeen-virhe';
$lang['UPLOAD_ERROR_SIZE'] = 'Lähetetyn tiedoston koko ylittää enimmäiskoon %s';
$lang['UPLOAD_ERROR_FORMAT'] = 'Virheellinen tiedostotyyppi kuva';
$lang['UPLOAD_ERROR_DIMENSIONS'] = 'Image dimensions exceed the maximum allowable %sx%s pixels';
$lang['UPLOAD_ERROR_NOT_IMAGE'] = 'Ladattu tiedosto ei ole kuva';
$lang['UPLOAD_ERROR_NOT_ALLOWED'] = 'Laajennus %s lataukset ei ole sallittua';
$lang['UPLOAD_ERRORS'] = [
    UPLOAD_ERR_INI_SIZE => 'olet ylittänyt maksimi tiedostokoko server',
    UPLOAD_ERR_FORM_SIZE => 'olet ylittänyt palvelimeen ladattavan tiedoston enimmäiskoko',
    UPLOAD_ERR_PARTIAL => 'tiedosto oli osittain ladattu',
    UPLOAD_ERR_NO_FILE => 'tiedostoa ei ladata',
    UPLOAD_ERR_NO_TMP_DIR => 'väliaikaista hakemistoa ei löytynyt',
    UPLOAD_ERR_CANT_WRITE => 'kirjoittaa virhe',
    UPLOAD_ERR_EXTENSION => 'lataa pysäytti laajennus',
];

// Captcha
$lang['CAPTCHA'] = 'Tarkista, että et ole robotti';
$lang['CAPTCHA_WRONG'] = 'Et voi vahvistaa, että et ole robotti';
$lang['CAPTCHA_SETTINGS'] = '<h2>Captcha is not fully configured</h2><p>Generate the keys using the dashboard of your captcha service, after you need to put them at the file library/config.php.</p>';
$lang['CAPTCHA_OCCURS_BACKGROUND'] = 'The CAPTCHA verification occurs in the background';

// Sending email
$lang['REPLY_TO'] = 'Reply to';
$lang['EMAILER_SUBJECT'] = [
    'EMPTY' => 'Ei aihetta',
    'GROUP_ADDED' => 'Sinut on lisätty käyttäjäryhmään',
    'GROUP_APPROVED' => 'Pyyntösi liittyä käyttäjäryhmään on hyväksytty',
    'GROUP_REQUEST' => 'Pyyntö liittyä käyttäjäryhmään',
    'PRIVMSG_NOTIFY' => 'Uusi yksityisviesti',
    'TOPIC_NOTIFY' => 'Notification of response in the thread - %s',
    'USER_ACTIVATE' => 'Tilin uudelleenaktivointi',
    'USER_ACTIVATE_PASSWD' => 'Uuden salasanan vahvistaminen',
    'USER_WELCOME' => 'Tervetuloa sivustolle %s',
    'USER_WELCOME_INACTIVE' => 'Tervetuloa sivustolle %s',
];

// Null ratio
$lang['BT_NULL_RATIO'] = 'Reset ratio';
$lang['BT_NULL_RATIO_NONE'] = 'You don\'t have a ratio';
$lang['BT_NULL_RATIO_ALERT'] = "Attention!\n\nAre you sure you want to reset your ratio?";
$lang['BT_NULL_RATIO_AGAIN'] = 'You have already reset your ratio!';
$lang['BT_NULL_RATIO_NOT_NEEDED'] = 'You have a good ratio. Reset is possible only with a ratio less than %s';
$lang['BT_NULL_RATIO_SUCCESS'] = 'The ratio has been reset successfully!';

// Releaser stats
$lang['RELEASER_STAT_SIZE'] = 'Total size:';
$lang['RELEASER_STAT'] = 'Releaser stats:';
$lang['RELEASER_STAT_SHOW'] = 'Show stats';
