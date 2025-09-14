<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

// Common, these terms are used extensively on several pages
$lang['ADMIN'] = 'Administrera';
$lang['FORUM'] = 'Forum';
$lang['CATEGORY'] = 'Kategori';
$lang['HIDE_CAT'] = 'Dölja kategorier';
$lang['HIDE_CAT_MESS'] = 'Vissa kategorier är dolda av anpassade visningsalternativ';
$lang['SHOW_ALL'] = 'Visa allt';
$lang['TOPIC'] = 'Ämne';
$lang['TOPICS'] = 'Ämnen';
$lang['TOPICS_SHORT'] = 'Ämnen';
$lang['REPLIES'] = 'Svar';
$lang['REPLIES_SHORT'] = 'Svar';
$lang['VIEWS'] = 'Visningar';
$lang['POSTS'] = 'Inlägg';
$lang['POSTS_SHORT'] = 'Inlägg';
$lang['POSTED'] = 'Inlagd';
$lang['USERNAME'] = 'Användarnamn';
$lang['PASSWORD'] = 'Lösenord';
$lang['PASSWORD_SHOW_BTN'] = 'Show password';
$lang['EMAIL'] = 'E-post';
$lang['PM'] = 'PM';
$lang['AUTHOR'] = 'Författare';
$lang['TIME'] = 'Tid';
$lang['HOURS'] = 'Timmar';
$lang['MESSAGE'] = 'Meddelande';
$lang['TORRENT'] = 'Torrent';
$lang['PERMISSIONS'] = 'Behörigheter';
$lang['TYPE'] = 'Typ';
$lang['SEEDER'] = 'Seeder';
$lang['LEECHER'] = 'Leecher';
$lang['RELEASER'] = 'Releaser';

$lang['1_DAY'] = '1 Dag';
$lang['7_DAYS'] = '7 Dagar';
$lang['2_WEEKS'] = '2 Veckor';
$lang['1_MONTH'] = '1 Månad';
$lang['3_MONTHS'] = '3 Månader';
$lang['6_MONTHS'] = '6 Månader';
$lang['1_YEAR'] = '1 År';

$lang['GO'] = 'Gå till';
$lang['SUBMIT'] = 'Skicka';
$lang['RESET'] = 'Återställ';
$lang['CANCEL'] = 'Avbryt';
$lang['PREVIEW'] = 'Förhandsgranska';
$lang['AJAX_PREVIEW'] = 'Quick View';
$lang['CONFIRM'] = 'Bekräfta';
$lang['YES'] = 'Ja';
$lang['NO'] = 'Inga';
$lang['ENABLED'] = 'Aktiverad';
$lang['DISABLED'] = 'Funktionshindrade';
$lang['ERROR'] = 'Fel';
$lang['SELECT_ACTION'] = 'Välj åtgärder';
$lang['CLEAR'] = 'Clear';
$lang['MOVE_TO_TOP'] = 'Move to top';
$lang['UNKNOWN'] = 'Okänd';
$lang['COPY_TO_CLIPBOARD'] = 'Copy to clipboard';
$lang['NO_ITEMS'] = 'There seems to be no data here...';
$lang['PLEASE_TRY_AGAIN'] = 'Please try again after few seconds...';

$lang['NEXT_PAGE'] = 'Nästa';
$lang['PREVIOUS_PAGE'] = 'Föregående';
$lang['SHORT_PAGE'] = 'sida';
$lang['GOTO_PAGE'] = 'Gå till sidan';
$lang['GOTO_SHORT'] = 'Sida';
$lang['JOINED'] = 'Gick';
$lang['LONGEVITY'] = 'Registrerade';
$lang['IP_ADDRESS'] = 'IP-Adress';
$lang['POSTED_AFTER'] = 'efter';

$lang['SELECT_FORUM'] = 'Välj forum';
$lang['VIEW_LATEST_POST'] = 'Visa senaste inlägg';
$lang['VIEW_NEWEST_POST'] = 'Visa senaste inlägg';
$lang['PAGE_OF'] = 'Sidan <b>%d</b> av <b>%s</b>';

$lang['ICQ'] = 'ICQ';

$lang['SKYPE'] = 'Skype';
$lang['SKYPE_ERROR'] = 'Du angav ett ogiltigt Skype logga in';

$lang['TWITTER'] = 'Twitter';
$lang['TWITTER_ERROR'] = 'Du angav ett ogiltigt Twitter logga in';

$lang['FORUM_INDEX'] = '%s Forum Index'; // e.g. sitename Forum Index, %s can be removed if you prefer

$lang['POST_NEW_TOPIC'] = 'Skapa nytt ämne';
$lang['POST_NEW_RELEASE'] = 'Post-ny utgåva';
$lang['POST_REGULAR_TOPIC'] = 'Inlägg återkommande ämne';
$lang['REPLY_TO_TOPIC'] = 'Svara på ämne';
$lang['REPLY_WITH_QUOTE'] = 'Svara med citat';

$lang['CLICK_RETURN_TOPIC'] = 'Klicka %sHere%s för att återgå till ämnet'; // %s's here are for uris, do not remove!
$lang['CLICK_RETURN_LOGIN'] = 'Klicka %sHere%s att försöka igen';
$lang['CLICK_RETURN_FORUM'] = 'Klicka %sHere%s för att återvända till forumet';
$lang['CLICK_VIEW_MESSAGE'] = 'Klicka %sHere%s för att återgå till ditt meddelande';
$lang['CLICK_RETURN_MODCP'] = 'Klicka %sHere%s för att återgå till Moderator Kontrollpanelen';
$lang['CLICK_RETURN_GROUP'] = 'Klicka %sHere%s för att återgå till gruppen information';

$lang['ADMIN_PANEL'] = 'Gå till Administration Panel';
$lang['ALL_CACHE_CLEARED'] = 'Cache-minnet har raderats';
$lang['ALL_TEMPLATE_CLEARED'] = 'Mall cache har rensats';
$lang['DATASTORE_CLEARED'] = 'Datastore har rensats';
$lang['BOARD_DISABLE'] = 'Ledsen, men detta forum är inaktiverad. Försök att komma tillbaka senare';
$lang['BOARD_DISABLE_CRON'] = 'Forumet är nere för underhåll. Försök att komma tillbaka senare';
$lang['ADMIN_DISABLE'] = 'forumet är avaktiverat av administratören, du kan aktivera den när som helst';
$lang['ADMIN_DISABLE_CRON'] = 'forum är låst med trigger cron-jobb, du kan ta bort en lock som helst';
$lang['ADMIN_DISABLE_TITLE'] = 'Forumet är funktionshindrade';
$lang['ADMIN_DISABLE_CRON_TITLE'] = 'Forumet är nere för underhåll';
$lang['ADMIN_UNLOCK'] = 'Aktivera forum';
$lang['ADMIN_UNLOCKED'] = 'Olåst';
$lang['ADMIN_UNLOCK_CRON'] = 'Ta bort lock';

$lang['LOADING'] = 'Laddar...';
$lang['JUMPBOX_TITLE'] = 'Välj forum';
$lang['DISPLAYING_OPTIONS'] = 'Visa alternativ';

// Global Header strings
$lang['REGISTERED_USERS'] = 'Registrerade Användare:';
$lang['BROWSING_FORUM'] = 'Användare som besöker detta forum:';
$lang['ONLINE_USERS'] = 'Totalt finns det <b>%1$d</b> användare online: %2$d registrerade och %3$d gäster';
$lang['RECORD_ONLINE_USERS'] = 'Flest användare online någonsin var <b>%s</b> på %s'; // first %s = number of users, second %s is the date.

$lang['ONLINE_ADMIN'] = 'Administratör';
$lang['ONLINE_MOD'] = 'Moderator';
$lang['ONLINE_GROUP_MEMBER'] = 'Grupp-medlem';

$lang['CANT_EDIT_IN_DEMO_MODE'] = 'This action can not be performed in demo mode!';

$lang['CURRENT_TIME'] = 'Aktuell tid är: <span class="tz_time">%s</span>';

$lang['SEARCH_NEW'] = 'Visa senaste inlägg';
$lang['SEARCH_SELF'] = 'Mina inlägg';
$lang['SEARCH_SELF_BY_LAST'] = 'senaste inlägg';
$lang['SEARCH_SELF_BY_MY'] = 'mina inlägg';
$lang['SEARCH_UNANSWERED'] = 'Visa obesvarade inlägg';
$lang['SEARCH_UNANSWERED_SHORT'] = 'obesvarade';
$lang['SEARCH_LATEST'] = 'Senaste ämnen';
$lang['LATEST_RELEASES'] = 'Senaste nyheterna';

$lang['REGISTER'] = 'Registrera dig';
$lang['PROFILE'] = 'Profil';
$lang['EDIT_PROFILE'] = 'Redigera profil';
$lang['SEARCH'] = 'Sök';
$lang['MEMBERLIST'] = 'Medlemslistan';
$lang['USERGROUPS'] = 'Användargrupper';
$lang['LASTPOST'] = 'Senaste Inlägg';
$lang['MODERATOR'] = 'Moderator';
$lang['MODERATORS'] = 'Moderatorer';
$lang['TERMS'] = 'Villkor';
$lang['NOTHING_HAS_CHANGED'] = 'Ingenting har ändrats';

// Stats block text
$lang['POSTED_TOPICS_TOTAL'] = 'Våra användare har skrivit totalt <b>%s</b> ämnen'; // Number of topics
$lang['POSTED_ARTICLES_ZERO_TOTAL'] = 'Våra användare har skrivit totalt <b>0</b> artiklar'; // Number of posts
$lang['POSTED_ARTICLES_TOTAL'] = 'Våra användare har skrivit totalt <b>%s</b> artiklar'; // Number of posts
$lang['REGISTERED_USERS_ZERO_TOTAL'] = 'Vi har <b>0</b> registrerade användare'; // # registered users
$lang['REGISTERED_USERS_TOTAL'] = 'Vi har <b>%s</b> registrerade användare'; // # registered users
$lang['USERS_TOTAL_GENDER'] = 'Pojkar: <b>%d</b>, Flickor: <b>%d</b>, Övriga: <b>%d</b>';
$lang['NEWEST_USER'] = 'Den senast registrerade användaren är <b>%s</b>'; // a href, username, /a

// Tracker stats
$lang['TORRENTS_STAT'] = 'Torrents: <b style="color: blue;">%s</b>,&nbsp; Total storlek: <b>%s</b>'; // first %s = number of torrents, second %s is the total size.
$lang['PEERS_STAT'] = 'Kamrater: <b>%s</b>,&nbsp; Såmaskiner: <b class="seedmed">%s</b>,&nbsp; Reciprokörer: <b class="leechmed">%s</b>'; // first %s = number of peers, second %s = number of seeders,  third %s = number of leechers.
$lang['SPEED_STAT'] = 'Totala hastighet: <b>%s</b>&nbsp;'; // %s = total speed.

$lang['NO_NEW_POSTS_LAST_VISIT'] = 'Inga nya inlägg sedan ditt senaste besök';
$lang['NO_NEW_POSTS'] = 'Inga nya inlägg';
$lang['NEW_POSTS'] = 'Nya inlägg';
$lang['NEW_POST'] = 'Nya inlägg';
$lang['NO_NEW_POSTS_HOT'] = 'Inga nya inlägg [ Populär ]';
$lang['NEW_POSTS_HOT'] = 'Nya inlägg [ Populär ]';
$lang['NEW_POSTS_LOCKED'] = 'Nya inlägg [ Låst ]';
$lang['FORUM_LOCKED_MAIN'] = 'Forum är låst';

// Login
$lang['ENTER_PASSWORD'] = 'Vänligen ange användarnamn och lösenord för att logga in.';
$lang['LOGIN'] = 'Logga in';
$lang['LOGOUT'] = 'Logga ut';
$lang['CONFIRM_LOGOUT'] = 'Är du säker på att du vill logga ut?';

$lang['FORGOTTEN_PASSWORD'] = 'Glömt ditt lösenord?';
$lang['AUTO_LOGIN'] = 'Logga in mig automatiskt';
$lang['ERROR_LOGIN'] = 'Det användarnamn som du lämnat är felaktig eller ogiltig eller lösenord är ogiltig.';
$lang['REMEMBER'] = 'Kom ihåg';
$lang['USER_WELCOME'] = 'Välkommen,';

// Index page
$lang['HOME'] = 'Hem';
$lang['NO_POSTS'] = 'Inga inlägg';
$lang['NO_FORUMS'] = 'Detta forum har inga forum';

$lang['PRIVATE_MESSAGE'] = 'Privata Meddelande';
$lang['PRIVATE_MESSAGES'] = 'Privata Meddelanden';
$lang['WHOSONLINE'] = 'Vem är online';

$lang['MARK_ALL_FORUMS_READ'] = 'Markera alla forum som lästa';
$lang['FORUMS_MARKED_READ'] = 'Alla forum som markerats som lästa';

$lang['LATEST_NEWS'] = 'Senaste nyheterna';
$lang['NETWORK_NEWS'] = 'Network news';
$lang['SUBFORUMS'] = 'Subforum';

// Viewforum
$lang['VIEW_FORUM'] = 'Visa Forum';

$lang['FORUM_NOT_EXIST'] = 'Det forum som du valt inte finns.';
$lang['REACHED_ON_ERROR'] = 'Du har nått den här sidan är fel.';
$lang['ERROR_PORNO_FORUM'] = 'Denna typ av forum (18+) som var gömd i din profil du';

$lang['DISPLAY_TOPICS'] = 'Visa ämnen';
$lang['ALL_TOPICS'] = 'Alla Ämnen';
$lang['MODERATE_FORUM'] = 'Måttlig detta forum';
$lang['TITLE_SEARCH_HINT'] = 'sök titel...';

$lang['TOPIC_ANNOUNCEMENT'] = 'Meddelande:';
$lang['TOPIC_STICKY'] = 'Sticky:';
$lang['TOPIC_MOVED'] = 'Flyttade:';
$lang['TOPIC_POLL'] = '[ Poll ]';

$lang['MARK_TOPICS_READ'] = 'Markera alla ämnen som lästa';
$lang['TOPICS_MARKED_READ'] = 'Ämnen i detta forum, har bara varit märkt läsa';

$lang['RULES_POST_CAN'] = 'Du <b>can</b> posta nya ämnen i detta forum';
$lang['RULES_POST_CANNOT'] = 'Du <b>cannot</b> posta nya ämnen i detta forum';
$lang['RULES_REPLY_CAN'] = 'Du <b>can</b> svara på inlägg i det här forumet';
$lang['RULES_REPLY_CANNOT'] = 'Du <b>cannot</b> svara på inlägg i det här forumet';
$lang['RULES_EDIT_CAN'] = 'Du <b>can</b> redigera dina inlägg i det här forumet';
$lang['RULES_EDIT_CANNOT'] = 'Du <b>cannot</b> redigera dina inlägg i det här forumet';
$lang['RULES_DELETE_CAN'] = 'Du <b>can</b> ta bort dina inlägg i det här forumet';
$lang['RULES_DELETE_CANNOT'] = 'Du <b>cannot</b> ta bort dina inlägg i det här forumet';
$lang['RULES_VOTE_CAN'] = 'Du <b>can</b> rösta i omröstningar i detta forum';
$lang['RULES_VOTE_CANNOT'] = 'Du <b>cannot</b> rösta i omröstningar i detta forum';
$lang['RULES_MODERATE'] = 'Du <b>can</b> måttlig detta forum';

$lang['NO_TOPICS_POST_ONE'] = 'There are no posts in this forum yet<br />Click on the <b>New Topic</b> icon, and your post will be the first.';
$lang['NO_RELEASES_POST_ONE'] = 'There are no releases in this forum yet<br />Click on the <b>New Release</b> icon, and your release will be the first.';

// Viewtopic
$lang['VIEW_TOPIC'] = 'Visa avsnittet';

$lang['GUEST'] = 'Gäst';
$lang['POST_SUBJECT'] = 'Inlägg ämnet';
$lang['SUBMIT_VOTE'] = 'Skicka omröstning';
$lang['VIEW_RESULTS'] = 'Visa resultat';

$lang['NO_NEWER_TOPICS'] = 'Det finns ingen nyare inlägg i det här forumet';
$lang['NO_OLDER_TOPICS'] = 'Det finns några äldre inlägg i det här forumet';
$lang['TOPIC_POST_NOT_EXIST'] = 'Det ämne eller inlägg som du har begärt inte finns';
$lang['NO_POSTS_TOPIC'] = 'Det finns inga inlägg i detta ämne';

$lang['DISPLAY_POSTS'] = 'Visa inlägg';
$lang['ALL_POSTS'] = 'Alla Inlägg';
$lang['NEWEST_FIRST'] = 'Nyaste Först';
$lang['OLDEST_FIRST'] = 'Äldst Först';

$lang['BACK_TO_TOP'] = 'Tillbaka till toppen';

$lang['READ_PROFILE'] = 'Visa användarens profil';
$lang['VISIT_WEBSITE'] = 'Besök affisch: s hemsida';
$lang['VIEW_IP'] = 'Se affisch IP-adress';
$lang['MODERATE_POST'] = 'Måttlig inlägg';
$lang['DELETE_POST'] = 'Ta bort detta inlägg';

$lang['WROTE'] = 'skrev'; // proceeds the username and is followed by the quoted text
$lang['QUOTE'] = 'Citat'; // comes before bbcode quote output
$lang['CODE'] = 'Kod'; // comes before bbcode code output
$lang['SPOILER_HEAD'] = 'dold text';
$lang['SPOILER_CLOSE'] = 'stäng';
$lang['PLAY_ON_CURPAGE'] = 'Börja spela upp på den aktuella sidan';

$lang['EDITED_TIME_TOTAL'] = 'Last edited by <b>%s</b> on %s; edited %d time in total'; // Last edited by me on 12 Oct 2001; edited 1 time in total
$lang['EDITED_TIMES_TOTAL'] = 'Last edited by <b>%s</b> on %s; edited %d times in total'; // Last edited by me on 12 Oct 2001; edited 2 times in total

$lang['LOCK_TOPIC'] = 'Lås ämnet';
$lang['UNLOCK_TOPIC'] = 'Låsa upp ämnet';
$lang['MOVE_TOPIC'] = 'Flytta ämnet';
$lang['DELETE_TOPIC'] = 'Ta bort ämnet';
$lang['SPLIT_TOPIC'] = 'Dela ämne';

$lang['STOP_WATCHING_TOPIC'] = 'Sluta följa ämne';
$lang['START_WATCHING_TOPIC'] = 'Följ ämne för svar';
$lang['NO_LONGER_WATCHING'] = 'Du är inte längre efter det här ämnet';
$lang['YOU_ARE_WATCHING'] = 'Du följer nu detta ämne';

$lang['TOTAL_VOTES'] = 'Röster';
$lang['SEARCH_IN_TOPIC'] = 'sök på ämne...';
$lang['HIDE_IN_TOPIC'] = 'Dölja';

$lang['SHOW'] = 'Visa';
$lang['AVATARS'] = 'Avatarer';
$lang['RANK_IMAGES'] = 'Rangordna bilder';
$lang['POST_IMAGES'] = 'Lägger ut bilder';
$lang['SIGNATURES'] = 'Signaturer';
$lang['SPOILER'] = 'Spoiler';
$lang['SHOW_OPENED'] = 'Visa öppnade';
$lang['DOWNLOAD_PIC'] = 'Nedladdningsbara bilder';

$lang['MODERATE_TOPIC'] = 'Måttlig detta ämne';
$lang['SELECT_POSTS_PER_PAGE'] = 'inlägg per sida';

// Posting/Replying (Not private messaging!)
$lang['TOPIC_REVIEW'] = 'Ämne recension';

$lang['NO_POST_MODE'] = 'Inga inlägg läget som är valt'; // If posting.php is called without a mode (newtopic/reply/delete/etc., shouldn't be shown normally)

$lang['POST_A_NEW_TOPIC'] = 'Skapa nytt ämne';
$lang['POST_A_REPLY'] = 'Inlägget nytt svar';
$lang['POST_TOPIC_AS'] = 'Inlägg ämne som';
$lang['EDIT_POST'] = 'Redigera inlägg';
$lang['EDIT_TOPIC_TITLE'] = 'Redigera ämne titel';
$lang['EDIT_POST_NOT_1'] = 'Du är inte tillåtna ';
$lang['EDIT_POST_NOT_2'] = 'Du kan inte ';
$lang['EDIT_POST_AJAX'] = 'Du kan inte redigera inlägg med status ';
$lang['AFTER_THE_LAPSE'] = 'efter förflutit ';

$lang['DONT_MESSAGE_TITLE'] = 'Du bör ange meddelandets titel';
$lang['INVALID_TOPIC_ID'] = 'Ämnet Är Frånvarande!';
$lang['INVALID_TOPIC_ID_DB'] = 'Ämne inte finns i databasen!';

$lang['NOT_POST'] = 'Frånvarande Meddelande';
$lang['NOT_EDIT_TOR_STATUS'] = 'Du kan inte redigera utgåvan med status';
$lang['TOR_STATUS_DAYS'] = 'dagar';

$lang['OPTIONS'] = 'Alternativ';

$lang['POST_ANNOUNCEMENT'] = 'Meddelande';
$lang['POST_STICKY'] = 'Sticky';
$lang['POST_NORMAL'] = 'Normal';
$lang['POST_DOWNLOAD'] = 'Ladda ner';

$lang['PRINT_PAGE'] = 'Print page';

$lang['CONFIRM_DELETE'] = 'Är du säker på att du vill radera detta inlägg?';
$lang['CONFIRM_DELETE_POLL'] = 'Är du säker på att du vill radera denna omröstning?';

$lang['FLOOD_ERROR'] = 'Du kan inte göra annat inlägg så snart efter din sista, vänligen försök igen om en liten stund';
$lang['EMPTY_SUBJECT'] = 'Du måste ange ett ämne';
$lang['EMPTY_MESSAGE'] = 'Du måste ange ett meddelande';
$lang['FORUM_LOCKED'] = 'Detta forum är låst: du kan inte posta eller svara redigera ämnen';
$lang['TOPIC_LOCKED'] = 'Detta ämne är låst: du kan inte redigera inlägg eller göra svar';
$lang['TOPIC_LOCKED_SHORT'] = 'Låst ämne';
$lang['NO_POST_ID'] = 'Du måste välja en post för att redigera';
$lang['NO_TOPIC_ID'] = 'Du måste välja ett ämne för att svara';
$lang['NO_VALID_MODE'] = 'Du kan endast inlägg, svara, redigera eller citat meddelanden. Gå tillbaka och försök igen';
$lang['NO_SUCH_POST'] = 'Det finns inget sådant inlägg. Gå tillbaka och försök igen';
$lang['EDIT_OWN_POSTS'] = 'Ledsen, men du kan bara redigera dina egna inlägg';
$lang['DELETE_OWN_POSTS'] = 'Ledsen, men du kan bara ta bort dina egna inlägg';
$lang['CANNOT_DELETE_REPLIED'] = 'Ledsen, men du kan inte ta bort inlägg som har svarat att';
$lang['CANNOT_DELETE_POLL'] = 'Ledsen, men du kan inte ta bort en aktiv undersökning';
$lang['EMPTY_POLL_TITLE'] = 'Du måste ange en titel för din enkät';
$lang['TO_FEW_POLL_OPTIONS'] = 'Du måste ange minst två omröstningsalternativ';
$lang['TO_MANY_POLL_OPTIONS'] = 'Du har försökt att gå in alltför många omröstningsalternativ';
$lang['POST_HAS_NO_POLL'] = 'Det här inlägget har ingen poll';
$lang['ALREADY_VOTED'] = 'Du har redan röstat på denna enkät';
$lang['NO_VOTE_OPTION'] = 'Du måste ange ett alternativ när vi röstar';
$lang['LOCKED_WARN'] = 'Du har postat i låst ämne!';

$lang['ADD_POLL'] = 'Lägga till en omröstning';
$lang['ADD_POLL_EXPLAIN'] = 'Om du inte vill lägga till en omröstning för att ditt ämne, lämna dessa fält tomma.';
$lang['POLL_QUESTION'] = 'Omröstningsfrågan';
$lang['POLL_OPTION'] = 'Valmöjligheterna';
$lang['ADD_OPTION'] = 'Lägg till';
$lang['UPDATE'] = 'Uppdatering';
$lang['POLL_FOR'] = 'Kör enkät för';
$lang['DAYS'] = 'Dagar';
$lang['POLL_FOR_EXPLAIN'] = '[ Ange 0 eller lämna tomt för en aldrig sinande poll ]';
$lang['DELETE_POLL'] = 'Ta bort enkät';

$lang['MAX_SMILIES_PER_POST'] = 'Emoticons gränsen för %s emoticons överskrids.';

$lang['ATTACH_SIGNATURE'] = 'Bifoga signatur (signaturer kan ändras i profil)';
$lang['NOTIFY'] = 'Meddela mig när på svar';
$lang['ALLOW_ROBOTS_INDEXING'] = 'Allow robots indexing this topic';

$lang['STORED'] = 'Ditt meddelande har införts med framgång.';
$lang['EDITED'] = 'Meddelandet har ändrats';
$lang['DELETED'] = 'Ditt meddelande har tagits bort.';
$lang['POLL_DELETE'] = 'Din enkät har tagits bort.';
$lang['VOTE_CAST'] = 'Din röst har gjutits.';

$lang['EMOTICONS'] = 'Emoticons';
$lang['MORE_EMOTICONS'] = 'Visa fler Emotikoner';

$lang['FONT_COLOR'] = 'Teckensnitt färg';
$lang['COLOR_DEFAULT'] = 'Standard';
$lang['COLOR_DARK_RED'] = 'Mörk Röd';
$lang['COLOR_RED'] = 'Röd';
$lang['COLOR_ORANGE'] = 'Orange';
$lang['COLOR_BROWN'] = 'Brun';
$lang['COLOR_YELLOW'] = 'Gul';
$lang['COLOR_GREEN'] = 'Grön';
$lang['COLOR_OLIVE'] = 'Olivolja';
$lang['COLOR_CYAN'] = 'Cyan';
$lang['COLOR_BLUE'] = 'Blå';
$lang['COLOR_DARK_BLUE'] = 'Mörk Blå';
$lang['COLOR_INDIGO'] = 'Indigo';
$lang['COLOR_VIOLET'] = 'Violett';
$lang['COLOR_WHITE'] = 'Vit';
$lang['COLOR_BLACK'] = 'Svart';

$lang['FONT_SIZE'] = 'Font storlek';
$lang['FONT_TINY'] = 'Små';
$lang['FONT_SMALL'] = 'Små';
$lang['FONT_NORMAL'] = 'Normal';
$lang['FONT_LARGE'] = 'Stora';
$lang['FONT_HUGE'] = 'Enorm';

$lang['STYLES_TIP'] = 'Tips: Stilar kan appliceras snabbt på markerad text.';

$lang['NEW_POSTS_PREVIEW'] = 'Ämnet har nya, redigerade eller olästa inlägg';

// Private Messaging
$lang['PRIVATE_MESSAGING'] = 'Privata Meddelanden';

$lang['NO_NEW_PM'] = 'inga nya meddelanden';

$lang['NEW_PMS_FORMAT'] = '<b>%1$s</b> %2$s'; // 1 new message
$lang['NEW_PMS_DECLENSION'] = ['nytt meddelande', 'nya meddelanden'];

$lang['UNREAD_PMS_FORMAT'] = '<b>%1$s</b> %2$s'; // 1 new message
$lang['UNREAD_PMS_DECLENSION'] = ['olästa', 'olästa'];

$lang['UNREAD_MESSAGE'] = 'Olästa meddelande';
$lang['READ_MESSAGE'] = 'Läsa meddelande';

$lang['READ_PM'] = 'Läsa meddelande';
$lang['POST_NEW_PM'] = 'Skicka meddelande';
$lang['POST_REPLY_PM'] = 'Svara på meddelande';
$lang['POST_QUOTE_PM'] = 'Citat meddelande';
$lang['EDIT_PM'] = 'Redigera meddelandet';

$lang['INBOX'] = 'Inkorg';
$lang['OUTBOX'] = 'Utkorg';
$lang['SAVEBOX'] = 'Säkerhetsbox';
$lang['SENTBOX'] = 'Sentbox';
$lang['FLAG'] = 'Flagga';
$lang['SUBJECT'] = 'Ämne';
$lang['FROM'] = 'Från';
$lang['TO'] = 'Till';
$lang['DATE'] = 'Datum';
$lang['MARK'] = 'Mark';
$lang['SENT'] = 'Skickat';
$lang['SAVED'] = 'Sparade';
$lang['DELETE_MARKED'] = 'Ta Bort Markerade';
$lang['DELETE_ALL'] = 'Ta Bort Alla';
$lang['SAVE_MARKED'] = 'Spara Markerade';
$lang['SAVE_MESSAGE'] = 'Spara Meddelande';
$lang['DELETE_MESSAGE'] = 'Radera Meddelandet';

$lang['DISPLAY_MESSAGES'] = 'Visa meddelanden'; // Followed by number of days/weeks/months
$lang['ALL_MESSAGES'] = 'Alla Meddelanden';

$lang['NO_MESSAGES_FOLDER'] = 'Det finns några meddelanden i den här mappen';

$lang['PM_DISABLED'] = 'Privata meddelanden har inaktiverats på detta forum.';
$lang['CANNOT_SEND_PRIVMSG'] = 'Ledsen, men de har administratören förhindrat just dig från att skicka privata meddelanden.';
$lang['NO_TO_USER'] = 'Du måste ange ett användarnamn som du vill skicka detta meddelande.';
$lang['NO_SUCH_USER'] = 'Ledsen, men ingen sådan användare finns.';

$lang['DISABLE_BBCODE_PM'] = 'Inaktivera BBCode i detta meddelande';
$lang['DISABLE_SMILIES_PM'] = 'Inaktivera Smilies i detta meddelande';

$lang['MESSAGE_SENT'] = '<b>Your meddelande har skickats.</b>';

$lang['CLICK_RETURN_INBOX'] = 'Gå tillbaka till din:<br /><br /> %s<b>Inbox</b>%s';
$lang['CLICK_RETURN_SENTBOX'] = '&nbsp;&nbsp; %s<b>Sentbox</b>%s';
$lang['CLICK_RETURN_OUTBOX'] = '&nbsp;&nbsp; %s<b>Outbox</b>%s';
$lang['CLICK_RETURN_SAVEBOX'] = '&nbsp;&nbsp; %s<b>Savebox</b>%s';
$lang['CLICK_RETURN_INDEX'] = '%sReturn till Index%s';

$lang['SEND_A_NEW_MESSAGE'] = 'Skicka ett privat meddelande';
$lang['SEND_A_REPLY'] = 'Svara på ett privat meddelande';
$lang['EDIT_MESSAGE'] = 'Redigera privat meddelande';

$lang['NOTIFICATION_SUBJECT'] = 'New Private Message has been received!';

$lang['FIND_USERNAME'] = 'Hitta ett användarnamn';
$lang['SELECT_USERNAME'] = 'Välj ett Användarnamn';
$lang['FIND'] = 'Hitta';
$lang['NO_MATCH'] = 'Inga träffar.';

$lang['NO_PM_ID'] = 'Vänligen ange inläggets ID';
$lang['NO_SUCH_FOLDER'] = 'Mappen finns inte';
$lang['NO_FOLDER'] = 'Vänligen ange vilken mapp';

$lang['MARK_ALL'] = 'Markera alla';
$lang['UNMARK_ALL'] = 'Avmarkera alla';

$lang['CONFIRM_DELETE_PM'] = 'Är du säker på att du vill ta bort det här meddelandet?';
$lang['CONFIRM_DELETE_PMS'] = 'Är du säker på att du vill ta bort dessa meddelanden?';

$lang['INBOX_SIZE'] = 'Din Inkorg full is<br /><b>%d%%</b>'; // e.g. Your Inbox is 50% full
$lang['SENTBOX_SIZE'] = 'Din Sentbox is<br /><b>%d%%</b> full';
$lang['SAVEBOX_SIZE'] = 'Din Säkerhetsbox full is<br /><b>%d%%</b>';

$lang['CLICK_VIEW_PRIVMSG'] = 'Klicka %sHere%s att besöka din Inkorg';

$lang['OUTBOX_EXPL'] = '';

// Profiles/Registration
$lang['VIEWING_USER_PROFILE'] = 'Visa profil :: %s';
$lang['VIEWING_MY_PROFILE'] = 'Min profil [ <a href="%s">Settings / Ändra profile</a> ]';

$lang['DISABLED_USER'] = 'Inaktivt konto';
$lang['MANAGE_USER'] = 'Administration';

$lang['PREFERENCES'] = 'Inställningar';
$lang['ITEMS_REQUIRED'] = 'Punkter som är markerade med en * är obligatoriska om inte annat anges.';
$lang['REGISTRATION_INFO'] = 'Registrering Information';
$lang['PROFILE_INFO'] = 'Profilinformation';
$lang['PROFILE_INFO_WARN'] = 'Offentligt tillgänglig information';
$lang['AVATAR_PANEL'] = 'Avatar kontrollpanelen';

$lang['WEBSITE'] = 'Hemsida';
$lang['LOCATION'] = 'Läge';
$lang['CONTACT'] = 'Kontakta';
$lang['EMAIL_ADDRESS'] = 'E-post adress';
$lang['SEND_PRIVATE_MESSAGE'] = 'Skicka ett privat meddelande';
$lang['HIDDEN_EMAIL'] = '[ Dolt ]';
$lang['INTERESTS'] = 'Intressen';
$lang['OCCUPATION'] = 'Yrke';
$lang['POSTER_RANK'] = 'Affisch rang';
$lang['AWARDED_RANK'] = 'Tilldelas rang';
$lang['SHOT_RANK'] = 'Skott rang';

$lang['TOTAL_POSTS'] = 'Totalt inlägg';
$lang['SEARCH_USER_POSTS'] = 'Hitta inlägg'; // Find all posts by username
$lang['SEARCH_USER_POSTS_SHORT'] = 'Hitta användaren inlägg';
$lang['SEARCH_USER_TOPICS'] = 'Hitta användare ämnen'; // Find all topics by username

$lang['NO_USER_ID_SPECIFIED'] = 'Ledsen, men den användaren existerar inte.';
$lang['WRONG_PROFILE'] = 'Du kan inte ändra en profil som inte är din egen.';

$lang['ONLY_ONE_AVATAR'] = 'Only one type of avatar can be specified';
$lang['FILE_NO_DATA'] = 'Filen på den WEBBADRESS som du gav innehåller inga uppgifter';
$lang['NO_CONNECTION_URL'] = 'En anslutning kan inte göras till den URL som du gav';
$lang['INCOMPLETE_URL'] = 'URL: en du angav är ofullständig';
$lang['NO_SEND_ACCOUNT_INACTIVE'] = 'Ledsen, men ditt lösenord inte kan läsas eftersom ditt konto är för närvarande inaktiv';
$lang['NO_SEND_ACCOUNT'] = 'Ledsen, men ditt lösenord inte kan läsas. Vänligen kontakta forum administratören för mer information';

$lang['ALWAYS_ADD_SIG'] = 'Bifoga alltid min signatur';
$lang['HIDE_PORN_FORUMS'] = 'Dölj innehåll 18+';
$lang['ADD_RETRACKER'] = 'Lägg till retracker i torrent-filer';
$lang['ALWAYS_NOTIFY'] = 'Alltid underrätta mig om svar';
$lang['ALWAYS_NOTIFY_EXPLAIN'] = 'Skickar ett e-postmeddelande när någon svarar på ett ämne du har skrivit i. Detta kan ändras när du gör ett inlägg.';

$lang['BOARD_LANG'] = 'Styrelsen språk';
$lang['GENDER'] = 'Kön';
$lang['GENDER_SELECT'] = [
    0 => 'Okänd',
    1 => 'Hane',
    2 => 'Kvinna'
];
$lang['MODULE_OFF'] = 'Modulen är inaktiverad!';

$lang['BIRTHDAY'] = 'Födelsedag';
$lang['HAPPY_BIRTHDAY'] = 'Grattis På Födelsedagen!';
$lang['WRONG_BIRTHDAY_FORMAT'] = 'Födelsedag format angavs felaktigt.';
$lang['AGE'] = 'Ålder';
$lang['BIRTHDAY_TO_HIGH'] = 'Ledsen, men denna webbplats, accepterar inte användaren äldre än %d år gammal';
$lang['BIRTHDAY_TO_LOW'] = 'Ledsen, men denna webbplats, accepterar inte användaren yonger än %d år gammal';
$lang['BIRTHDAY_TODAY'] = 'Användare med en födelsedag idag: ';
$lang['BIRTHDAY_WEEK'] = 'Användare med en födelsedag inom de närmaste %d dagar: %s';
$lang['NOBIRTHDAY_WEEK'] = 'Inga användare har födelsedag de kommande %d dagar'; // %d is substitude with the number of days
$lang['NOBIRTHDAY_TODAY'] = 'Inga användare har födelsedag idag';
$lang['BIRTHDAY_ENABLE'] = 'Aktivera födelsedag';
$lang['BIRTHDAY_MAX_AGE'] = 'Max ålder';
$lang['BIRTHDAY_MIN_AGE'] = 'Min ålder';
$lang['BIRTHDAY_CHECK_DAY'] = 'Dagar på sig att kontrollera för kommande födelsedagar';
$lang['YEARS'] = 'År';

$lang['NO_THEMES'] = 'Några Teman I databasen';
$lang['TIMEZONE'] = 'Tidszon';
$lang['DATE_FORMAT_PROFILE'] = 'Datum format';
$lang['DATE_FORMAT_EXPLAIN'] = 'Den syntax som används är identiska till PHP <a href=\'https://www.php.net/manual/en/function.date.php\' target=\'_other\'>date()</a> funktion.';
$lang['SIGNATURE'] = 'Signatur';
$lang['SIGNATURE_EXPLAIN'] = 'Detta är ett stycke text som kan läggas till i inlägg du gör. Det är en %d tecken';
$lang['SIGNATURE_DISABLE'] = 'Undertecknat av för brott mot regler för forumet';
$lang['PUBLIC_VIEW_EMAIL'] = 'Visa e-post adress i min profil';

$lang['EMAIL_EXPLAIN'] = 'På denna adress du skickas att slutföra registreringen';

$lang['CURRENT_PASSWORD'] = 'Nuvarande lösenord';
$lang['NEW_PASSWORD'] = 'Nytt lösenord';
$lang['CONFIRM_PASSWORD'] = 'Bekräfta lösenord';
$lang['CONFIRM_PASSWORD_EXPLAIN'] = 'Du måste bekräfta ditt nuvarande lösenord om du vill ändra den eller ändra din e-post adress';
$lang['PASSWORD_IF_CHANGED'] = 'Du behöver bara ange ett lösenord om du vill ändra det';
$lang['PASSWORD_CONFIRM_IF_CHANGED'] = 'Du behöver bara bekräfta ditt lösenord om du har ändrat det ovan';

$lang['AUTOLOGIN'] = 'Autologin';
$lang['RESET_AUTOLOGIN'] = 'Återställ autologin nyckel';
$lang['RESET_AUTOLOGIN_EXPL'] = 'inklusive alla de platser som du har besökt forumet aktiverats auto-login';

$lang['AVATAR'] = 'Avatar';
$lang['AVATAR_EXPLAIN'] = 'Displays a small graphic image below your details in posts. Only one image can be displayed at a time, its width can be no greater than %d pixels, the height no greater than %d pixels, and the file size no more than %s.';
$lang['AVATAR_DELETE'] = 'Ta bort avatar';
$lang['AVATAR_DISABLE'] = 'Avatar kontroll alternativet är inaktiverat för kränkning <a href="%s"><b>forum rules</b></a>';
$lang['UPLOAD_AVATAR_FILE'] = 'Ladda upp avatar';

$lang['SELECT_AVATAR'] = 'Välj avatar';
$lang['RETURN_PROFILE'] = 'Tillbaka till profil';
$lang['SELECT_CATEGORY'] = 'Välj kategori';

$lang['DELETE_IMAGE'] = 'Radera bild';
$lang['SET_MONSTERID_AVATAR'] = 'Set MonsterID avatar';
$lang['CURRENT_IMAGE'] = 'Aktuella bilden';

$lang['NOTIFY_ON_PRIVMSG'] = 'Anmäla på nytt privat meddelande';
$lang['HIDE_USER'] = 'Dölj din online status';
$lang['HIDDEN_USER'] = 'Dold användare';

$lang['PROFILE_UPDATED'] = 'Om din profil har uppdaterats';
$lang['PROFILE_UPDATED_INACTIVE'] = 'Om din profil har uppdaterats. Men du har ändrat viktiga detaljer, vilket ditt konto är inaktivt nu. Kolla din e-post för att ta reda på hur du återaktiverar ditt konto, eller om admin aktivering krävs, vänta på att administratören att aktivera den igen.';

$lang['PASSWORD_MISMATCH'] = 'Lösenorden du angav matchar inte.';
$lang['CURRENT_PASSWORD_MISMATCH'] = 'Den nuvarande lösenord du angav matchar inte att lagras i databasen.';
$lang['PASSWORD_LONG'] = 'Your password must be no longer than %d characters and no shorter than %d characters.';
$lang['TOO_MANY_REGISTERS'] = 'Du har gjort alltför många registrering försök. Vänligen försök igen senare.';
$lang['USERNAME_TAKEN'] = 'Ledsen, men detta användarnamn har redan vidtagits.';
$lang['USERNAME_INVALID'] = 'Ledsen, men detta användarnamn innehåller ett ogiltigt tecken';
$lang['USERNAME_DISALLOWED'] = 'Ledsen, men detta användarnamn har nekats.';
$lang['USERNAME_TOO_LONG'] = 'Ditt namn är för lång.';
$lang['USERNAME_TOO_SMALL'] = 'Ditt namn är för liten.';
$lang['EMAIL_TAKEN'] = 'Ledsen, men denna e-postadress är redan registrerad på en användare.';
$lang['EMAIL_BANNED'] = 'Ledsen, men <b>%s</b> adress har förbjudits.';
$lang['EMAIL_INVALID'] = 'Ledsen, men denna e-postadress är ogiltig.';
$lang['EMAIL_TOO_LONG'] = 'Din e-post är för lång.';
$lang['SIGNATURE_TOO_LONG'] = 'Din signatur är för lång.';
$lang['SIGNATURE_ERROR_HTML'] = 'Signaturen kan endast innehålla BBCode';
$lang['FIELDS_EMPTY'] = 'Du måste fylla i de fält som krävs.';

$lang['WELCOME_SUBJECT'] = 'Välkommen till %s Forum'; // Welcome to my.com forums
$lang['NEW_ACCOUNT_SUBJECT'] = 'Nytt användarkonto';
$lang['ACCOUNT_ACTIVATED_SUBJECT'] = 'Kontot Aktiveras';

$lang['ACCOUNT_ADDED'] = 'Tack för din registrering. Ditt konto har skapats. Du kan nu logga in med ditt användarnamn och lösenord';
$lang['ACCOUNT_INACTIVE'] = 'Ditt konto har skapats. Men detta forum kräver att kontoaktivering. En aktiveringskoden har skickats till den e-postadress du angivit. Vänligen kontrollera din e-post för ytterligare information';
$lang['ACCOUNT_ACTIVE'] = 'Ditt konto har bara varit aktiverad. Tack för din registrering';
$lang['REACTIVATE'] = 'Återaktivera ditt konto!';
$lang['ALREADY_ACTIVATED'] = 'Du har redan aktiverat ditt konto';

$lang['REGISTRATION'] = 'Registrering Avtalsvillkor';

$lang['WRONG_ACTIVATION'] = 'Den aktiveringsnyckel du angav matchar inte något i databasen.';
$lang['SEND_PASSWORD'] = 'Skicka mig ett nytt lösenord';
$lang['PASSWORD_UPDATED'] = 'Ett nytt lösenord har skapats, var god kontrollera din e-post för information om hur du aktiverar det.';
$lang['NO_EMAIL_MATCH'] = 'Den e-postadress du angav matchar inte det som anges för det användarnamnet.';
$lang['NEW_PASSWORD_ACTIVATION'] = 'Nytt lösenord aktivering';
$lang['PASSWORD_ACTIVATED'] = 'Ditt konto har varit re-aktiveras. Att logga in, vänligen använd lösenord som medföljer i e-postmeddelandet du fått.';

$lang['SEND_EMAIL_MSG'] = 'Skicka ett e-postmeddelande';
$lang['NO_USER_SPECIFIED'] = 'Ingen användare var som anges';
$lang['USER_PREVENT_EMAIL'] = 'Denna användaren inte vill ta emot e-post. Prova att skicka dem ett privat meddelande.';
$lang['USER_NOT_EXIST'] = 'Användaren existerar inte';
$lang['EMAIL_MESSAGE_DESC'] = 'Det här meddelandet kommer att skickas som oformaterad text, så du behöver inte innehålla någon HTML eller BBCode. Returadress för detta meddelande kommer att ställa till din e-post adress.';
$lang['FLOOD_EMAIL_LIMIT'] = 'Du kan inte skicka ett e-mail vid denna tid. Försök igen senare.';
$lang['RECIPIENT'] = 'Mottagaren';
$lang['EMAIL_SENT'] = 'E-postmeddelandet har skickats.';
$lang['SEND_EMAIL'] = 'Skicka e-post';
$lang['EMPTY_SUBJECT_EMAIL'] = 'Du måste ange ett ämne för e-post.';
$lang['EMPTY_MESSAGE_EMAIL'] = 'Du måste ange ett meddelande som ska skickas via e-post.';

$lang['USER_AGREEMENT'] = 'Användaravtal';
$lang['USER_AGREEMENT_HEAD'] = 'För att fortsätta måste du acceptera följande regler';
$lang['USER_AGREEMENT_AGREE'] = 'Jag har läst och accepterar Användaren avtalet ovan';

$lang['COPYRIGHT_HOLDERS'] = 'Upphovsrätt';
$lang['ADVERT'] = 'Annonsera på denna webbplats';
$lang['NOT_FOUND'] = 'Filen kunde inte hittas';

// Memberslist
$lang['SORT'] = 'Sortera';
$lang['SORT_TOP_TEN'] = 'Topp Tio Affischer';
$lang['SORT_JOINED'] = 'Datum Gick Med';
$lang['SORT_USERNAME'] = 'Användarnamn';
$lang['SORT_LOCATION'] = 'Läge';
$lang['SORT_POSTS'] = 'Totalt inlägg';
$lang['SORT_EMAIL'] = 'E-post';
$lang['SORT_WEBSITE'] = 'Hemsida';
$lang['ASC'] = 'Stigande';
$lang['DESC'] = 'Fallande';
$lang['ORDER'] = 'Beställning';

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
$lang['GROUP_CONTROL_PANEL'] = 'Användargrupper';
$lang['GROUP_CONFIGURATION'] = 'Grupp Konfiguration';
$lang['GROUP_GOTO_CONFIG'] = 'Gå till Gruppen Konfiguration panel';
$lang['GROUP_RETURN'] = 'Återgå till Gruppen Användare sidan';
$lang['MEMBERSHIP_DETAILS'] = 'Medlemskap I Gruppen För Detaljer';
$lang['JOIN_A_GROUP'] = 'Gå med i en Grupp';

$lang['GROUP_INFORMATION'] = 'Gruppen Information';
$lang['GROUP_NAME'] = 'Grupp namn';
$lang['GROUP_DESCRIPTION'] = 'Gruppen beskrivning';
$lang['GROUP_SIGNATURE'] = 'Grupp signatur';
$lang['GROUP_MEMBERSHIP'] = 'Medlemskap i gruppen';
$lang['GROUP_MEMBERS'] = 'Medlemmar I Gruppen';
$lang['GROUP_MODERATOR'] = 'Gruppen Moderator';
$lang['PENDING_MEMBERS'] = 'I Avvaktan Medlemmar';

$lang['GROUP_TIME'] = 'Skapad';
$lang['RELEASE_GROUP'] = 'Release Group';

$lang['GROUP_TYPE'] = 'Gruppen typ';
$lang['GROUP_OPEN'] = 'Öppen grupp';
$lang['GROUP_CLOSED'] = 'Sluten grupp';
$lang['GROUP_HIDDEN'] = 'Dolda gruppen';

$lang['GROUP_MEMBER_MOD'] = 'Gruppen moderator';
$lang['GROUP_MEMBER_MEMBER'] = 'Nuvarande medlemskap';
$lang['GROUP_MEMBER_PENDING'] = 'Medlemskap i avvaktan på';
$lang['GROUP_MEMBER_OPEN'] = 'Öppna grupper';
$lang['GROUP_MEMBER_CLOSED'] = 'Slutna grupper';
$lang['GROUP_MEMBER_HIDDEN'] = 'Dolda grupper';

$lang['NO_GROUPS_EXIST'] = 'Finns Inga Grupper';
$lang['GROUP_NOT_EXIST'] = 'Som grupp existerar inte';
$lang['NO_GROUP_ID_SPECIFIED'] = 'Grupp-ID är inte specificerat';

$lang['NO_GROUP_MEMBERS'] = 'Den här gruppen har inga medlemmar';
$lang['HIDDEN_GROUP_MEMBERS'] = 'Denna grupp är dold, du kan inte visa sitt medlemskap';
$lang['NO_PENDING_GROUP_MEMBERS'] = 'Den här gruppen har inga väntande medlemmar';
$lang['GROUP_JOINED'] = 'Du har framgångsrikt tecknat abonnemang för den här gruppen.<br />You kommer att meddelas när din prenumeration är som godkänts av koncernen moderator.';
$lang['GROUP_REQUEST'] = 'En begäran att ansluta sig till din grupp har gjorts.';
$lang['GROUP_APPROVED'] = 'Din begäran har godkänts.';
$lang['GROUP_ADDED'] = 'Du har lagts till denna användargrupp.';
$lang['ALREADY_MEMBER_GROUP'] = 'Är du redan medlem i denna grupp';
$lang['USER_IS_MEMBER_GROUP'] = '%s is already a member of this group';
$lang['USER_IS_MOD_GROUP'] = '%s is a moderator of this group';
$lang['GROUP_TYPE_UPDATED'] = 'Uppdaterat grupp typ.';
$lang['EFFECTIVE_DATE'] = 'Datum';

$lang['COULD_NOT_ADD_USER'] = 'Den användare som du valt inte finns.';
$lang['COULD_NOT_ANON_USER'] = 'Du kan inte göra en Anonym medlem i gruppen.';

$lang['CONFIRM_UNSUB'] = 'Är du säker på att du vill avregistrera dig från denna grupp?';
$lang['CONFIRM_UNSUB_PENDING'] = 'Din prenumeration för att denna grupp har ännu inte godkänts, är du säker på att du vill avsluta prenumerationen?';

$lang['UNSUB_SUCCESS'] = 'Du har varit fn-tecknade från denna grupp.';

$lang['APPROVE_SELECTED'] = 'Godkänna Markerade';
$lang['DENY_SELECTED'] = 'Förneka Utvalda';
$lang['NOT_LOGGED_IN'] = 'Du måste vara inloggad för att gå med i en grupp.';
$lang['REMOVE_SELECTED'] = 'Ta Bort Markerade';
$lang['ADD_MEMBER'] = 'Lägg Till Medlem';
$lang['NOT_GROUP_MODERATOR'] = 'Du är inte här gruppens moderator, därför att du inte kan utföra denna åtgärd.';

$lang['LOGIN_TO_JOIN'] = 'Logga in för att gå med i eller hantera gruppmedlemskap';
$lang['THIS_OPEN_GROUP'] = 'Detta är en öppen grupp: klicka på för att ansöka om medlemskap';
$lang['THIS_CLOSED_GROUP'] = 'Detta är en stängd grupp: inga fler användare accepterat';
$lang['THIS_HIDDEN_GROUP'] = 'Detta är en dold grupp: automatisk användare tillägg är inte tillåtna';
$lang['MEMBER_THIS_GROUP'] = 'Du är en medlem av den här gruppen';
$lang['PENDING_THIS_GROUP'] = 'Ditt medlemskap i denna grupp är väntan';
$lang['ARE_GROUP_MODERATOR'] = 'Du är den grupp som moderator';
$lang['NONE'] = 'Ingen';

$lang['SUBSCRIBE'] = 'Prenumerera';
$lang['UNSUBSCRIBE_GROUP'] = 'Avsluta prenumerationen';
$lang['VIEW_INFORMATION'] = 'Visa Information';
$lang['MEMBERS_IN_GROUP'] = 'Medlemmar i gruppen';

// Release Groups
$lang['POST_RELEASE_FROM_GROUP'] = 'Efter frigörande från gruppen';
$lang['CHOOSE_RELEASE_GROUP'] = 'inte valda';
$lang['ATTACH_RG_SIG'] = 'bifoga release group signatur';
$lang['RELEASE_FROM_RG'] = 'Utgåvan har utarbetats av';
$lang['GROUPS_RELEASES'] = 'Koncernens utsläpp';
$lang['MORE_RELEASES'] = 'Hitta alla utgåvor av den grupp';
$lang['NOT_A_RELEASE_GROUP'] = 'Denna grupp är inte en release group';

// Search
$lang['SEARCH_OFF'] = 'Sök är tillfälligt inaktiverad';
$lang['SEARCH_ERROR'] = 'Just nu är sökmotorn inte available<br /><br />Try att upprepa begäran efter flera sekunder';
$lang['SEARCH_HELP_URL'] = 'Sök Hjälp';
$lang['SEARCH_QUERY'] = 'Sök Fråga';
$lang['SEARCH_OPTIONS'] = 'Sök Alternativ';

$lang['SEARCH_WORDS'] = 'Sök efter Nyckelord';
$lang['SEARCH_WORDS_EXPL'] = 'Du kan använda <b>+</b> att definiera ord som måste finnas i sökresultatet och <b>-</b> att definiera ord som inte bör vara på resultatet (ex: "+word1 -word2"). Använd * som jokertecken för partiella matchningar';
$lang['SEARCH_AUTHOR'] = 'Sök efter Författare';
$lang['SEARCH_AUTHOR_EXPL'] = 'Använd * som jokertecken för partiella matchningar';

$lang['SEARCH_TITLES_ONLY'] = 'Sök på ämne titlar bara';
$lang['SEARCH_ALL_WORDS'] = 'alla ord';
$lang['SEARCH_MY_MSG_ONLY'] = 'Sök bara i mina inlägg';
$lang['IN_MY_POSTS'] = 'I mina inlägg';
$lang['SEARCH_MY_TOPICS'] = 'i mina ämnen';
$lang['NEW_TOPICS'] = 'Nya ämnen';

$lang['RETURN_FIRST'] = 'Avkastning första'; // followed by xxx characters in a select box
$lang['CHARACTERS_POSTS'] = 'tecken på inlägg';

$lang['SEARCH_PREVIOUS'] = 'Sök föregående';

$lang['SORT_BY'] = 'Sortera efter';
$lang['SORT_TIME'] = 'Inlägg';
$lang['SORT_POST_SUBJECT'] = 'Inlägg Ämnet';
$lang['SORT_TOPIC_TITLE'] = 'Ämne Rubrik';
$lang['SORT_AUTHOR'] = 'Författare';
$lang['SORT_FORUM'] = 'Forum';

$lang['DISPLAY_RESULTS_AS'] = 'Visa resultat';
$lang['ALL_AVAILABLE'] = 'Alla tillgängliga';
$lang['BRIEFLY'] = 'Kortfattat';
$lang['NO_SEARCHABLE_FORUMS'] = 'Om du inte har behörighet att söka till något forum på denna webbplats.';

$lang['NO_SEARCH_MATCH'] = 'Inga trådar eller inlägg som träffade din sökkriterier';
$lang['FOUND_SEARCH_MATCH'] = 'Sökning hittade %d match'; // e.g. Search found 1 match
$lang['FOUND_SEARCH_MATCHES'] = 'Sökning hittade %d matcher'; // e.g. Search found 24 matches
$lang['TOO_MANY_SEARCH_RESULTS'] = 'För många resultat kan hittas, var god försök att vara mer specifik';

$lang['CLOSE_WINDOW'] = 'Stäng Fönstret';
$lang['CLOSE'] = 'stäng';
$lang['HIDE'] = 'dölja';
$lang['SEARCH_TERMS'] = 'Sök fråga';

// Auth related entries
// Note the %s will be replaced with one of the following 'user' arrays
$lang['SORRY_AUTH_VIEW'] = 'Ledsen, men endast %s kan visa detta forum.';
$lang['SORRY_AUTH_READ'] = 'Ledsen, men endast %s kan läsa inlägg i det här forumet.';
$lang['SORRY_AUTH_POST'] = 'Ledsen, men endast %s kan posta inlägg i det här forumet.';
$lang['SORRY_AUTH_REPLY'] = 'Ledsen, men endast %s kan svara på inlägg i det här forumet.';
$lang['SORRY_AUTH_EDIT'] = 'Ledsen, men endast %s kan redigera inlägg i det här forumet.';
$lang['SORRY_AUTH_DELETE'] = 'Ledsen, men endast %s kan ta bort inlägg i detta forum.';
$lang['SORRY_AUTH_VOTE'] = 'Ledsen, men endast %s kan rösta i omröstningar i detta forum.';
$lang['SORRY_AUTH_STICKY'] = 'Ledsen, men endast %s kan posta klibbiga meddelanden i detta forum.';
$lang['SORRY_AUTH_ANNOUNCE'] = 'Ledsen, men endast %s kan posta meddelanden i detta forum.';

// These replace the %s in the above strings
$lang['AUTH_ANONYMOUS_USERS'] = '<b>anonymous users</b>';
$lang['AUTH_REGISTERED_USERS'] = '<b>registered users</b>';
$lang['AUTH_USERS_GRANTED_ACCESS'] = '<b>users beviljats särskilda access</b>';
$lang['AUTH_MODERATORS'] = '<b>moderators</b>';
$lang['AUTH_ADMINISTRATORS'] = '<b>administrators</b>';

$lang['NOT_MODERATOR'] = 'Du är inte moderator i detta forum.';
$lang['NOT_AUTHORISED'] = 'Inte Godkänd';

$lang['YOU_BEEN_BANNED'] = 'Du har blivit avstängd från detta forum. Kontakta administratören för mer information.';

// Viewonline
$lang['ONLINE_EXPLAIN'] = 'aktiva användare under de senaste fem minuter';
$lang['LAST_UPDATED'] = 'Senast Uppdaterad';

// Moderator Control Panel
$lang['MOD_CP'] = 'Moderator Kontrollpanelen';
$lang['MOD_CP_EXPLAIN'] = 'Med formuläret nedan kan du utföra en massa måtta verksamhet på detta forum. Du kan låsa, låsa upp, flytta eller ta bort valfritt antal ämnen.';

$lang['SELECT'] = 'Välj';
$lang['DELETE'] = 'Ta bort';
$lang['MOVE'] = 'Flytta';
$lang['LOCK'] = 'Lås';
$lang['UNLOCK'] = 'Låsa upp';

$lang['TOPICS_REMOVED'] = 'Den valda ämnen har tagits bort från databasen.';
$lang['NO_TOPICS_REMOVED'] = 'Några ämnen som togs bort.';
$lang['TOPICS_LOCKED'] = 'Den valda teman har varit låst.';
$lang['TOPICS_MOVED'] = 'Den valda ämnen har flyttats.';
$lang['TOPICS_UNLOCKED'] = 'Den valda teman har varit olåst.';
$lang['NO_TOPICS_MOVED'] = 'Inga ämnen som har flyttat.';

$lang['CONFIRM_DELETE_TOPIC'] = 'Är du säker på att du vill ta bort det valda ämnet/s?';
$lang['CONFIRM_LOCK_TOPIC'] = 'Är du säker på att du vill låsa den valda ämne/s?';
$lang['CONFIRM_UNLOCK_TOPIC'] = 'Är du säker på att du vill låsa upp det markerade avsnittet/s?';
$lang['CONFIRM_MOVE_TOPIC'] = 'Är du säker på att du vill flytta den valda ämne/s?';

$lang['MOVE_TO_FORUM'] = 'Gå till forum';
$lang['LEAVE_SHADOW_TOPIC'] = 'Lämna shadow ämne i gamla forumet.';

$lang['SPLIT_TOPIC_EXPLAIN'] = 'Med formuläret nedan kan du dela upp ett meddelande i två, antingen genom att välja inlägg individuellt eller genom att dela upp på en markerad post';
$lang['NEW_TOPIC_TITLE'] = 'Nytt ämne titel';
$lang['FORUM_FOR_NEW_TOPIC'] = 'Forum för nytt ämne';
$lang['SPLIT_POSTS'] = 'Dela utvalda inlägg';
$lang['SPLIT_AFTER'] = 'Split från utvalda inlägg';
$lang['TOPIC_SPLIT'] = 'Den valda ämnet har varit framgångsrikt split';

$lang['TOO_MANY_ERROR'] = 'Du har valt för många inlägg. Du kan endast välja en post för att dela upp ett ämne efter!';

$lang['NONE_SELECTED'] = 'Du har inget valt att utföra denna operation. Gå tillbaka och välj minst ett.';
$lang['NEW_FORUM'] = 'Nytt forum';

$lang['THIS_POSTS_IP'] = 'IP-adress för det här inlägget';
$lang['OTHER_IP_THIS_USER'] = 'Andra IP-adresser som användaren har postat från';
$lang['USERS_THIS_IP'] = 'Användare inlägg från den här IP-adressen';
$lang['IP_INFO'] = 'IP-Information';
$lang['LOOKUP_IP'] = 'Leta upp IP-adress';

// Timezones ... for display on each page
$lang['ALL_TIMES'] = 'Alla tider är <span class="tz_time">%s</span>'; // This is followed by UTC and the timezone offset

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

$lang['DATETIME']['TODAY'] = 'Idag';
$lang['DATETIME']['YESTERDAY'] = 'Igår';

$lang['DATETIME']['SUNDAY'] = 'Söndag';
$lang['DATETIME']['MONDAY'] = 'Måndag';
$lang['DATETIME']['TUESDAY'] = 'Tisdag';
$lang['DATETIME']['WEDNESDAY'] = 'Onsdag';
$lang['DATETIME']['THURSDAY'] = 'Torsdag';
$lang['DATETIME']['FRIDAY'] = 'Fredag';
$lang['DATETIME']['SATURDAY'] = 'Lördag';
$lang['DATETIME']['SUN'] = 'Solen';
$lang['DATETIME']['MON'] = 'Mon';
$lang['DATETIME']['TUE'] = 'Tue';
$lang['DATETIME']['WED'] = 'Wed';
$lang['DATETIME']['THU'] = 'Thu';
$lang['DATETIME']['FRI'] = 'Fre';
$lang['DATETIME']['SAT'] = 'Lör';
$lang['DATETIME']['JANUARY'] = 'Januari';
$lang['DATETIME']['FEBRUARY'] = 'Februari';
$lang['DATETIME']['MARCH'] = 'Mars';
$lang['DATETIME']['APRIL'] = 'April';
$lang['DATETIME']['MAY'] = 'Maj';
$lang['DATETIME']['JUNE'] = 'Juni';
$lang['DATETIME']['JULY'] = 'Juli';
$lang['DATETIME']['AUGUST'] = 'Augusti';
$lang['DATETIME']['SEPTEMBER'] = 'September';
$lang['DATETIME']['OCTOBER'] = 'Oktober';
$lang['DATETIME']['NOVEMBER'] = 'November';
$lang['DATETIME']['DECEMBER'] = 'December';
$lang['DATETIME']['JAN'] = 'Jan';
$lang['DATETIME']['FEB'] = 'Feb';
$lang['DATETIME']['MAR'] = 'Mar';
$lang['DATETIME']['APR'] = 'Apr';
$lang['DATETIME']['JUN'] = 'Jun';
$lang['DATETIME']['JUL'] = 'Jul';
$lang['DATETIME']['AUG'] = 'Aug';
$lang['DATETIME']['SEP'] = 'Sep';
$lang['DATETIME']['OCT'] = 'Okt';
$lang['DATETIME']['NOV'] = 'Nov';
$lang['DATETIME']['DEC'] = 'Dec';

// Country selector
$lang['COUNTRY'] = 'Country';
$lang['SET_OWN_COUNTRY'] = 'Set own country (Manually)';
$lang['COUNTRIES'] = [
    0 => 'Välj',
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
$lang['INFORMATION'] = 'Information';
$lang['ADMIN_REAUTHENTICATE'] = 'För att administrera/måttlig styrelsen måste du autentisera dig själv.';

// Attachment Mod Main Language Variables
// Auth Related Entries
$lang['RULES_ATTACH_CAN'] = 'Du <b>can</b> bifoga filer i detta forum';
$lang['RULES_ATTACH_CANNOT'] = 'Du <b>cannot</b> bifoga filer i detta forum';
$lang['RULES_DOWNLOAD_CAN'] = 'Du <b>can</b> ladda ner filer i detta forum';
$lang['RULES_DOWNLOAD_CANNOT'] = 'Du <b>cannot</b> ladda ner filer i detta forum';
$lang['SORRY_AUTH_VIEW_ATTACH'] = 'Ledsen, men du har inte behörighet att visa eller ladda ner denna Bilaga';

// Viewtopic -> Display of Attachments
$lang['DESCRIPTION'] = 'Beskrivning'; // used in Administration Panel too...
$lang['DOWNLOAD'] = 'Ladda ner'; // this Language Variable is defined in admin.php too, but we are unable to access it from the main Language File
$lang['FILESIZE'] = 'Filstorlek';
$lang['VIEWED'] = 'Ses';
$lang['EXTENSION_DISABLED_AFTER_POSTING'] = 'Förlängning "%s\' har inaktiverats av en administratör, därför denna Bilaga visas inte.'; // used in Posts and PM's, replace %s with mime type

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

$lang['ATTACHMENT'] = 'Bifogade filer';
$lang['ATTACHMENT_THUMBNAIL'] = 'Bilaga Miniatyr';

// Posting/PM -> Posting Attachments
$lang['ADD_ATTACHMENT'] = 'Lägg Till Bilaga';
$lang['ADD_ATTACHMENT_TITLE'] = 'Lägga till en Bifogad fil';
$lang['ADD_ATTACHMENT_EXPLAIN'] = 'Om du inte vill lägga till en Bifogad fil till ditt Inlägg, vänligen lämna dessa Fält tomma';
$lang['FILENAME'] = 'Filnamn';
$lang['FILE_COMMENT'] = 'Fil Kommentar';

// Posting/PM -> Posted Attachments
$lang['POSTED_ATTACHMENTS'] = 'Postat Bifogade Filer';
$lang['UPDATE_COMMENT'] = 'Uppdatera Kommentar';
$lang['DELETE_ATTACHMENTS'] = 'Ta Bort Bifogade Filer';
$lang['DELETE_ATTACHMENT'] = 'Ta Bort Bifogad Fil';
$lang['DELETE_THUMBNAIL'] = 'Ta Bort Miniatyrbild';
$lang['UPLOAD_NEW_VERSION'] = 'Ladda Upp Ny Version';

// Errors -> Posting Attachments
$lang['INVALID_FILENAME'] = '%s är ett ogiltigt filnamn'; // replace %s with given filename
$lang['ATTACHMENT_PHP_SIZE_NA'] = 'Den Bifogade filen är för stor.<br />Could inte få den maximala Storleken definieras i PHP.<br />The Bilaga Mod är inte att bestämma den maximala Ladda Storlek definieras i php.ini.';
$lang['ATTACHMENT_PHP_SIZE_OVERRUN'] = 'Den Bifogade filen är för stor.<br />Maximum Ladda Storlek: %d MB.<br />Please observera att denna Storlek är definierade i php.ini, detta betyder att det är satt av PHP och den Bifogade filen Mod kan inte åsidosätta detta värde.'; // replace %d with ini_get('upload_max_filesize')
$lang['DISALLOWED_EXTENSION'] = 'Förlängning %s är inte tillåtna'; // replace %s with extension (e.g. .php)
$lang['DISALLOWED_EXTENSION_WITHIN_FORUM'] = 'Du är inte tillåtet att publicera Filer med Filändelsen %s inom detta Forum'; // replace %s with the Extension
$lang['ATTACHMENT_TOO_BIG'] = 'The Attachment is too big.<br />Max Size: %s'; // replace %d with maximum file size, %s with size var
$lang['ATTACH_QUOTA_REACHED'] = 'Ledsen, men den maximala filstorleken för Bilagor som ska nås. Kontakta Administratören om du har frågor.';
$lang['TOO_MANY_ATTACHMENTS'] = 'Kvarstad får inte läggas till, eftersom max. antal %d Bilagor i detta inlägg var uppnås'; // replace %d with maximum number of attachments
$lang['ERROR_IMAGESIZE'] = 'Den Bifogade filen/Bilden måste vara mindre än %d pixlar bred och %d pixlar hög';
$lang['GENERAL_UPLOAD_ERROR'] = 'Ladda upp Fel: Kunde inte ladda upp en Bilaga till %s.'; // replace %s with local path

$lang['ERROR_EMPTY_ADD_ATTACHBOX'] = 'Du måste ange värden i " Lägga till en Bifogad fil i Rutan';
$lang['ERROR_MISSING_OLD_ENTRY'] = 'Det går inte att Uppdatera Bifogad fil, kunde inte hitta gamla Bifogad fil Inlägg';

// Errors -> PM Related
$lang['ATTACH_QUOTA_SENDER_PM_REACHED'] = 'Ledsen, men den maximala filstorleken för Bilagor i ditt Privata Meddelande Mapp har uppnåtts. Vänligen ta bort några av dina emot/sända Bilagor.';
$lang['ATTACH_QUOTA_RECEIVER_PM_REACHED'] = 'Ledsen, men den maximala filstorleken för Bilagor i Privat Meddelande Mapp "%s\' har nåtts. Låt honom veta, eller vänta tills han/hon har raderat en del av hans/hennes Bilagor.';

// Errors -> Download
$lang['NO_ATTACHMENT_SELECTED'] = 'Du har inte valt en bifogad fil för att hämta eller visa.';
$lang['ERROR_NO_ATTACHMENT'] = 'Den markerade Bilagan finns inte längre';

// Delete Attachments
$lang['CONFIRM_DELETE_ATTACHMENTS'] = 'Är du säker på att du vill ta bort den valda Bilagor?';
$lang['DELETED_ATTACHMENTS'] = 'Den valda Bilagor har tagits bort.';
$lang['ERROR_DELETED_ATTACHMENTS'] = 'Kunde inte ta bort Bilagor.';
$lang['CONFIRM_DELETE_PM_ATTACHMENTS'] = 'Är du säker på att du vill ta bort alla Bilagor postat i detta PM?';

// General Error Messages
$lang['ATTACHMENT_FEATURE_DISABLED'] = 'Bilagan-Funktionen är avaktiverad.';

$lang['DIRECTORY_DOES_NOT_EXIST'] = 'Katalogen \'%s\' finns inte eller Kunde inte hittas.'; // replace %s with directory
$lang['DIRECTORY_IS_NOT_A_DIR'] = 'Kontrollera om det är "%s" är en katalog.'; // replace %s with directory
$lang['DIRECTORY_NOT_WRITEABLE'] = 'Katalogen \'%s" är inte skrivbar. Du måste skapa ladda upp vägen och chmod den till 777 (eller ändra ägaren för att du httpd-servrar ägare) för att ladda upp filer.<br />If du har bara vanlig FTP-åtkomst ändra \'Attribut\' av katalogen för att rwxrwxrwx.'; // replace %s with directory

// Quota Variables
$lang['UPLOAD_QUOTA'] = 'Ladda Upp Kvot';
$lang['PM_QUOTA'] = 'PM Kvot';

// Common Variables
$lang['BYTES'] = 'Byte';
$lang['KB'] = 'KB';
$lang['MB'] = 'MB';
$lang['GB'] = 'GB';
$lang['ATTACH_SEARCH_QUERY'] = 'Sök Bifogade Filer';
$lang['TEST_SETTINGS'] = 'Inställningar För Test';
$lang['NOT_ASSIGNED'] = 'Inte Tilldelat';
$lang['NO_FILE_COMMENT_AVAILABLE'] = 'Ingen Fil Kommentar finns';
$lang['ATTACHBOX_LIMIT'] = 'Din Attachbox is<br /><b>%d%%</b> full';
$lang['NO_QUOTA_LIMIT'] = 'Ingen Kvot';
$lang['UNLIMITED'] = 'Obegränsat';

//bt
$lang['BT_REG_YES'] = 'Registrerade';
$lang['BT_REG_NO'] = 'Inte är registrerat';
$lang['BT_ADDED'] = 'Läggas till';
$lang['BT_REG_ON_TRACKER'] = 'Registrera dig på tracker';
$lang['BT_REG_FAIL'] = 'Kunde inte registrera sig torrent på tracker';
$lang['BT_REG_FAIL_SAME_HASH'] = 'En annan torrent med samma info_hash redan <a href="%s"><b>registered</b></a>';
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
$lang['BT_UNREG_FROM_TRACKER'] = 'Ta bort från tracker';
$lang['BT_UNREGISTERED'] = 'Torrent oregistrerade';
$lang['BT_UNREGISTERED_ALREADY'] = 'Torrent already unregistered';
$lang['BT_REGISTERED'] = 'Torrent registrerade på tracker<br /><br />Now du behöver för att <a href="%s"><b>download din torrent</b></a> och köra den med din BitTorrent klient att välja mappen med de ursprungliga filerna du delar eftersom ladda ner vägen';
$lang['INVALID_ANN_URL'] = 'Ogiltiga Announce URL [%s]<br /><br />must vara <b>%s</b>';
$lang['PASSKEY_ERR_TOR_NOT_REG'] = 'Kunde inte lägga passkey<br /><br />Torrent som inte är registrerade på tracker';
$lang['BT_PASSKEY'] = 'Passkey';
$lang['BT_GEN_PASSKEY'] = 'skapa en ny';
$lang['BT_PASSKEY_VIEW'] = 'visa';
$lang['BT_GEN_PASSKEY_NEW'] = "Uppmärksamhet! Efter att ändra nytt lösenord, kommer du behöver för att åter ladda ner alla aktiva torrents! \n Är du säker på att du vill skapa ett nytt lösenord?";
$lang['BT_NO_SEARCHABLE_FORUMS'] = 'Inga sökbara forum som finns';

$lang['SEEDS'] = 'Frö';
$lang['LEECHS'] = 'Leech';
$lang['SPEED_UP'] = 'Hastighet Upp';
$lang['SPEED_DOWN'] = 'Hastighet Ner';

$lang['SEEDERS'] = 'Såmaskiner';
$lang['LEECHERS'] = 'Leechers';
$lang['RELEASING'] = 'Själv';
$lang['SEEDING'] = 'Frö';
$lang['LEECHING'] = 'Leech';
$lang['IS_REGISTERED'] = 'Registrerade';
$lang['MAGNET'] = 'Magnet-link';
$lang['MAGNET_FOR_GUESTS'] = 'Show magnet-link for guests';
$lang['MAGNET_v2'] = 'Magnet-link (BitTorrent v2 supported)';

//torrent status mod
$lang['TOR_STATUS'] = 'Status';
$lang['TOR_STATUS_SELECT_ACTION'] = 'Välj status';
$lang['TOR_STATUS_NOT_SELECT'] = 'Du har inte valt status.';
$lang['TOR_STATUS_SELECT_ALL'] = 'Alla statusar';
$lang['TOR_STATUS_FORBIDDEN'] = 'This topic\'s status is: ';
$lang['TOR_STATUS_NAME'] = [
    TOR_NOT_APPROVED => 'inte kontrolleras',
    TOR_CLOSED => 'stängt',
    TOR_APPROVED => 'kollade',
    TOR_NEED_EDIT => 'inte formaliserat tills',
    TOR_NO_DESC => 'inte formaliserade',
    TOR_DUP => 'duplicate',
    TOR_CLOSED_CPHOLD => 'closed (copyright)',
    TOR_CONSUMED => 'absorberas',
    TOR_DOUBTFUL => 'tveksamt',
    TOR_CHECKING => 'being checked',
    TOR_TMP => 'tillfällig',
    TOR_PREMOD => 'pre-måtta',
    TOR_REPLENISH => 'replenishing',
];
$lang['TOR_STATUS_FAILED'] = 'Denna status finns inte!';
$lang['TORRENT_FAILED'] = 'Fördelningen var inte hittas!';
$lang['TOR_STATUS_DUB'] = 'Distributionen har samma status';
$lang['TOR_DONT_CHANGE'] = 'Change of status can not be performed!';
$lang['TOR_STATUS_OF'] = 'Distributionen har den status:';
$lang['TOR_STATUS_CHANGED'] = 'Status ändras: ';
$lang['TOR_BACK'] = ' tillbaka';
$lang['PROCEED'] = 'Gå vidare';
$lang['INVALID_ATTACH_ID'] = 'Saknas filen identifierare!';
$lang['CHANGE_TOR_TYPE'] = 'Typ torrent ändrats';
$lang['DEL_TORRENT'] = 'Är du säker på att du vill ta bort torrent?';
$lang['DEL_MOVE_TORRENT'] = 'Är du säker på att du vill ta bort och flytta ämnet?';
$lang['UNEXECUTED_RELEASE'] = 'Har du en oformlig release innan du skapar en ny fix hans oformliga!';
$lang['TOR_STATUS_LOG_ACTION'] = 'New status: %s.<br/>Previous status: %s.';

// tor_comment
$lang['TOR_MOD_TITLE'] = 'Ändra status för distribution - %s';
$lang['TOR_MOD_MSG'] = "Hej, %s.\n\n Status [url=%s]your[/url] fördelningen ändras till [b]%s[/b]";

$lang['TOR_AUTH_TITLE'] = 'Förändringar i design - %s';
$lang['TOR_AUTH_MSG'] = "Hej, %s.\n\n att Göra min fördelningen förändrats - [url=%s]%s[/url]\n\n du åter kontrollera det.";
$lang['TOR_AUTH_FIXED'] = 'Fast';
$lang['TOR_AUTH_SENT_COMMENT'] = ' &middot; <span class="seed bold">The information som skickas till moderator. Förvänta dig.</span>';

$lang['BT_TOPIC_TITLE'] = 'Ämne rubrik';
$lang['BT_SEEDER_LAST_SEEN'] = 'Utsäde senast sedd';
$lang['BT_SORT_FORUM'] = 'Forum';
$lang['SIZE'] = 'Storlek';
$lang['PIECE_LENGTH'] = 'Bit längd';
$lang['COMPLETED'] = 'Completed downloads';
$lang['ADDED'] = 'Läggas till';
$lang['DELETE_TORRENT'] = 'Ta bort torrent';
$lang['DELETE_MOVE_TORRENT'] = 'Ta bort och flytta ämne';
$lang['DL_TORRENT'] = 'Ladda ner .torrent';
$lang['BT_LAST_POST'] = 'Senaste inlägg';
$lang['BT_CREATED'] = 'Publicerat ämne';
$lang['BT_REPLIES'] = 'Svar';
$lang['BT_VIEWS'] = 'Visningar';

// Gold/Silver releases
$lang['GOLD'] = 'Guld';
$lang['SILVER'] = 'Silver';
$lang['SET_GOLD_TORRENT'] = 'Gör guld';
$lang['UNSET_GOLD_TORRENT'] = 'Förgör guld';
$lang['SET_SILVER_TORRENT'] = 'Gör silver';
$lang['UNSET_SILVER_TORRENT'] = 'Förgör silver';
$lang['GOLD_STATUS'] = 'GULD TORRENT! LADDA NER TRAFIKEN INTE TÄNKA PÅ!';
$lang['SILVER_STATUS'] = 'SILVER TORRENT! LADDA NER TRAFIKEN DELVIS BEAKTAS!';
$lang['TOR_TYPE_LOG_ACTION'] = 'Torrent type changed to: %s';

$lang['TORRENT_STATUS'] = 'Search by status of release';
$lang['SEARCH_IN_FORUMS'] = 'Sök i Forum';
$lang['SELECT_CAT'] = 'Välj kategori';
$lang['GO_TO_SECTION'] = 'Gå till avsnittet';
$lang['TORRENTS_FROM'] = 'Inlägg från';
$lang['SHOW_ONLY'] = 'Visa endast';
$lang['SHOW_COLUMN'] = 'Visa kolumn';
$lang['SEL_CHAPTERS'] = 'Länk till de valda partitionerna';
$lang['NOT_SEL_CHAPTERS'] = 'Du har inte valt ämnen';
$lang['SEL_CHAPTERS_HELP'] = 'Du kan välja högst %s partition';
$lang['HIDE_CONTENTS'] = 'Dölja innehållet i {...}';
$lang['FILTER_BY_NAME'] = '<i>Filter med namn </i>';

$lang['BT_ONLY_ACTIVE'] = 'Aktiv';
$lang['BT_ONLY_MY'] = 'Mina pressmeddelanden';
$lang['BT_SEED_EXIST'] = 'Seeder finns';
$lang['BT_ONLY_NEW'] = 'Nytt från förra besök';
$lang['BT_SHOW_CAT'] = 'Kategori';
$lang['BT_SHOW_FORUM'] = 'Forum';
$lang['BT_SHOW_AUTHOR'] = 'Författare';
$lang['BT_SHOW_SPEED'] = 'Hastighet';
$lang['SEED_NOT_SEEN'] = 'Seeder inte sett';
$lang['TITLE_MATCH'] = 'Titel match';
$lang['BT_USER_NOT_FOUND'] = 'hittades inte';
$lang['DL_SPEED'] = 'Generellt nedladdningshastighet';

$lang['BT_DISREGARD'] = 'bortser';
$lang['BT_NEVER'] = 'aldrig';
$lang['BT_ALL_DAYS_FOR'] = 'hela tiden';
$lang['BT_1_DAY_FOR'] = 'sista dagen';
$lang['BT_3_DAY_FOR'] = 'de senaste tre dagarna';
$lang['BT_7_DAYS_FOR'] = 'förra veckan';
$lang['BT_2_WEEKS_FOR'] = 'sista två veckor';
$lang['BT_1_MONTH_FOR'] = 'senaste månaden';
$lang['BT_1_DAY'] = '1 dag';
$lang['BT_3_DAYS'] = '3 dagar';
$lang['BT_7_DAYS'] = 'vecka';
$lang['BT_2_WEEKS'] = '2 veckor';
$lang['BT_1_MONTH'] = 'månad';

$lang['DL_LIST_AND_TORRENT_ACTIVITY'] = 'DL-Lista och Torrent aktivitet';
$lang['DLWILL'] = 'Kommer att ladda ner';
$lang['DLDOWN'] = 'Ladda ner';
$lang['DLCOMPLETE'] = 'Komplett';
$lang['DLCANCEL'] = 'Avbryt';

$lang['DL_LIST_DEL'] = 'Klart DL-Lista';
$lang['DL_LIST_DEL_CONFIRM'] = 'Ta bort DL-Lista för detta ämne?';
$lang['SHOW_DL_LIST'] = 'Visa DL-Lista';
$lang['SET_DL_STATUS'] = 'Ladda ner';
$lang['UNSET_DL_STATUS'] = 'Inte Ladda Ner';
$lang['TOPICS_DOWN_SETS'] = 'Ämne status ändras till <b>Download</b>';
$lang['TOPICS_DOWN_UNSETS'] = '<b>Download</b> status bort';

$lang['TOPIC_DL'] = 'DL';

$lang['MY_DOWNLOADS'] = 'Mina Nedladdningar';
$lang['SEARCH_DL_WILL'] = 'Planerat';
$lang['SEARCH_DL_WILL_DOWNLOADS'] = 'Planerade Nedladdningar';
$lang['SEARCH_DL_DOWN'] = 'Nuvarande';
$lang['SEARCH_DL_COMPLETE'] = 'Klar';
$lang['SEARCH_DL_COMPLETE_DOWNLOADS'] = 'Färdiga Nedladdningar';
$lang['SEARCH_DL_CANCEL'] = 'Avbokning';
$lang['CUR_DOWNLOADS'] = 'Aktuella Nedladdningar';
$lang['CUR_UPLOADS'] = 'Nuvarande Inlagda';
$lang['SEARCH_RELEASES'] = 'Släpper';
$lang['TOR_SEARCH_TITLE'] = 'Sök alternativ';
$lang['OPEN_TOPIC'] = 'Öppet ämne';

$lang['ALLOWED_ONLY_1ST_POST_ATTACH'] = 'Posta torrents accepteras endast i första inlägget';
$lang['ALLOWED_ONLY_1ST_POST_REG'] = 'Registrera torrents accepteras bara från första inlägget';
$lang['REG_NOT_ALLOWED_IN_THIS_FORUM'] = 'Kunde inte registrera sig torrent i detta forum';
$lang['ALREADY_REG'] = 'Torrent som redan är registrerat';
$lang['NOT_TORRENT'] = 'Den här filen är inte torrent';
$lang['ONLY_1_TOR_PER_POST'] = 'Du kan endast registrera en torrent i ett inlägg';
$lang['ONLY_1_TOR_PER_TOPIC'] = 'Du kan endast registrera en torrent i ett ämne';
$lang['VIEWING_USER_BT_PROFILE'] = 'Torrent-profile';
$lang['CUR_ACTIVE_DLS'] = 'Aktiva torrents';

$lang['TD_TRAF'] = 'Idag';
$lang['YS_TRAF'] = 'Igår';
$lang['TOTAL_TRAF'] = 'Totalt';

$lang['USER_RATIO'] = 'Förhållandet';
$lang['MAX_SPEED'] = 'Hastighet';
$lang['DOWNLOADED'] = 'Hämtade';
$lang['UPLOADED'] = 'Uppladdade';
$lang['RELEASED'] = 'Släppt';
$lang['BONUS'] = 'Vid de sällsynta';
$lang['IT_WILL_BE_DOWN'] = 'det kommer att börja betraktas som efter att det kommer att laddas ner';
$lang['SPMODE_FULL'] = 'Visa kamrater i full detaljer';

// Seed Bonus
$lang['MY_BONUS'] = 'Min bonus (%s bonus i lager)';
$lang['BONUS_SELECT'] = 'Välj';
$lang['SEED_BONUS'] = 'Frö bonus';
$lang['EXCHANGE'] = 'Utbyte';
$lang['EXCHANGE_BONUS'] = 'Utbyte av utsäde bonusar';
$lang['BONUS_UPLOAD_DESC'] = '<b>%s att distribution</b> <br /> Att utbyta bonuspoäng på %1$s trafik som kommer att läggas till summan av din distribution.';
$lang['BONUS_UPLOAD_PRICE'] = '<b class="%s">%s</b>';
$lang['PRICE'] = 'Pris';
$lang['EXCHANGE_NOT'] = 'Utbyte inte tillgängligt';
$lang['BONUS_SUCCES'] = 'Att du är det framgångsrikt värvning %s';
$lang['BONUS_NOT_SUCCES'] = '<span class="leech">You inte har bonusar som finns tillgängliga. Mer sådd!</span>';
$lang['BONUS_RETURN'] = 'Tillbaka till utsäde bonus utbyte';

$lang['TRACKER'] = 'Tracker';
$lang['RANDOM_RELEASE'] = 'Random release';
$lang['OPEN_TOPICS'] = 'Öppna frågor';
$lang['OPEN_IN_SAME_WINDOW'] = 'öppna i samma fönster';
$lang['SHOW_TIME_TOPICS'] = 'visa tid av skapande ämnen';
$lang['SHOW_CURSOR'] = 'markera raden under markören';

$lang['BT_LOW_RATIO_FOR_DL'] = "Med förhållandet <b>%s</b> du kan inte ladda ner torrents";
$lang['BT_RATIO_WARNING_MSG'] = 'Om din förhållandet faller under %s, du kommer inte att kunna ladda ner Torrents! <a href="%s"><b>More om betyg.</b></a>';

$lang['SEEDER_LAST_SEEN'] = 'Seeder inte sett: <b>%s</b>';

$lang['NEED_TO_LOGIN_FIRST'] = 'Du behöver logga in först';
$lang['ONLY_FOR_MOD'] = 'Detta alternativ endast för moderatorer';
$lang['ONLY_FOR_ADMIN'] = 'Detta alternativ endast för administratörer';
$lang['ONLY_FOR_SUPER_ADMIN'] = 'Detta alternativ endast för superadministratörer';

$lang['LOGS'] = 'Ämnet historia';
$lang['FORUM_LOGS'] = 'Historia Forum';
$lang['AUTOCLEAN'] = 'Autoclean';
$lang['DESIGNER'] = 'Designer';

$lang['LAST_IP'] = 'Sista IP:';
$lang['REG_IP'] = 'Registrering av IP:';
$lang['OTHER_IP'] = 'Andra IP:';
$lang['ALREADY_REG_IP'] = 'Med din IP-adressen är redan registrerad användare %s. Om du inte tidigare har registrerat sig på vår tracker, e-post att <a href="mailto:%s">Administrator</a>';
$lang['HIDDEN'] = 'Dold';

// from admin
$lang['NOT_ADMIN'] = 'Du har inte behörighet att administrera denna styrelse';

$lang['COOKIES_REQUIRED'] = 'Cookies måste vara aktiverat!';
$lang['SESSION_EXPIRED'] = 'Sessionen gått ut';

// Sort memberlist per letter
$lang['POST_LINK'] = 'Skicka länk';
$lang['GOTO_QUOTED_POST'] = 'Gå till den citerade inlägg';
$lang['LAST_VISITED'] = 'Senast Besökt';
$lang['LAST_ACTIVITY'] = 'Senaste aktivitet';
$lang['NEVER'] = 'Aldrig';

//mpd
$lang['DELETE_POSTS'] = 'Ta bort valda inlägg';
$lang['DELETE_POSTS_SUCCESFULLY'] = 'Den valda inlägg har tagits bort';
$lang['NO_POSTS_REMOVED'] = 'No posts were removed.';

//ts
$lang['TOPICS_ANNOUNCEMENT'] = 'Meddelanden';
$lang['TOPICS_STICKY'] = 'Stickies';
$lang['TOPICS_NORMAL'] = 'Ämnen';

//dpc
$lang['DOUBLE_POST_ERROR'] = 'Du kan inte göra ett annat inlägg med exakt samma text som förra.';

//upt
$lang['UPDATE_POST_TIME'] = 'Uppdatering inlägg';

$lang['TOPIC_SPLIT_NEW'] = 'Nytt ämne';
$lang['TOPIC_SPLIT_OLD'] = 'Gammalt ämne';
$lang['BOT_LEAVE_MSG_MOVED'] = 'Lägg till bot-meddelande om att flytta';
$lang['BOT_REASON_MOVED'] = 'Reason to move';
$lang['BOT_AFTER_SPLIT_TO_OLD'] = 'Lägg till bot-meddelande om split till <b>old topic</b>';
$lang['BOT_AFTER_SPLIT_TO_NEW'] = 'Lägg till bot-meddelande om split till <b>new topic</b>';
//qr
$lang['QUICK_REPLY'] = 'Snabbt Svar';
$lang['INS_NAME_TIP'] = 'Infoga namn eller markerad text.';
$lang['QUOTE_SELECTED'] = 'Utvalda citat';
$lang['QR_ATTACHSIG'] = 'Bifoga en signatur';
$lang['QR_NOTIFY'] = 'Meddela svara';
$lang['QR_DISABLE'] = 'Inaktivera';
$lang['QR_USERNAME'] = 'Namn';
$lang['NO_TEXT_SEL'] = 'Välj en text som helst på en sida och försök igen';
$lang['QR_FONT_SEL'] = 'Font face';
$lang['QR_COLOR_SEL'] = 'Font color';
$lang['QR_SIZE_SEL'] = 'Font storlek';
$lang['COLOR_STEEL_BLUE'] = 'Blå Stål';
$lang['COLOR_GRAY'] = 'Grå';
$lang['COLOR_DARK_GREEN'] = 'Mörk Grön';

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

$lang['DECLENSION']['REPLIES'] = ['svara', 'svar'];
$lang['DECLENSION']['TIMES'] = ['tid', 'gånger'];
$lang['DECLENSION']['FILES'] = ['file', 'files'];

$lang['DELTA_TIME']['INTERVALS'] = [
    'seconds' => ['andra', 'sekunder'],
    'minutes' => ['minuter', 'minuter'],
    'hours' => ['timme', 'timmar'],
    'mday' => ['dag', 'dagar'],
    'mon' => ['månad', 'månader'],
    'year' => ['år', 'år'],
];
$lang['DELTA_TIME']['FORMAT'] = '%1$s %2$s'; // 5(%1) minutes(%2)

$lang['AUTH_TYPES'][AUTH_ALL] = $lang['AUTH_ANONYMOUS_USERS'];
$lang['AUTH_TYPES'][AUTH_REG] = $lang['AUTH_REGISTERED_USERS'];
$lang['AUTH_TYPES'][AUTH_ACL] = $lang['AUTH_USERS_GRANTED_ACCESS'];
$lang['AUTH_TYPES'][AUTH_MOD] = $lang['AUTH_MODERATORS'];
$lang['AUTH_TYPES'][AUTH_ADMIN] = $lang['AUTH_ADMINISTRATORS'];

$lang['NEW_USER_REG_DISABLED'] = 'Ledsen, registrering är inaktiverad vid denna tid';
$lang['ONLY_NEW_POSTS'] = 'bara nya inlägg';
$lang['ONLY_NEW_TOPICS'] = 'bara nya ämnen';

$lang['TORHELP_TITLE'] = 'Snälla hjälp till att seeda dessa torrents!';
$lang['STATISTICS'] = 'Statistik';
$lang['STATISTIC'] = 'Statistik';
$lang['VALUE'] = 'Värde';
$lang['INVERT_SELECT'] = 'Invertera markering';
$lang['STATUS'] = 'Status';
$lang['LAST_CHANGED_BY'] = 'Senast ändrad av';
$lang['CHANGES'] = 'Förändringar';
$lang['ACTION'] = 'Åtgärd';
$lang['REASON'] = 'Skäl';
$lang['COMMENT'] = 'Kommentar';

// search
$lang['SEARCH_S'] = 'sök...';
$lang['FORUM_S'] = 'på forum';
$lang['TRACKER_S'] = 'på tracker';
$lang['HASH_S'] = 'genom att info_hash';

// copyright
$lang['NOTICE'] = '!UPPMÄRKSAMHET!';
$lang['COPY'] = 'Webbplatsen ger inte elektroniska versioner av produkter, och är engagerade bara i en insamling och katalogisering av de referenser som sänds och publiceras på ett forum av våra läsare. Om du är den juridiska ägaren av alla insända materialet och inte vill att den hänvisning till att det var i vårt sortiment, kontakta oss så kommer vi omedelbart att ta bort henne. Filer för ett utbyte på tracker ges av användare av en webbplats, och den administration som inte bär ansvaret för deras underhåll. Begäran att inte fylla i de filer som är skyddade av upphovsrätt, och även filer av den illegala underhåll!';

// FILELIST
$lang['COLLAPSE'] = 'Kollaps katalog';
$lang['EXPAND'] = 'Expandera';
$lang['SWITCH'] = 'Stäng';
$lang['TOGGLE_WINDOW_FULL_SIZE'] = 'Increase/decrease the window';
$lang['EMPTY_ATTACH_ID'] = 'Saknas filen identifierare!';
$lang['TOR_NOT_FOUND'] = 'Fil saknas på servern!';
$lang['ERROR_BUILD'] = 'Innehållet i denna torrent-fil kan inte visas på sajten (det var inte möjligt att bygga en lista av filer)';
$lang['TORFILE_INVALID'] = 'Torrent-filen är skadad';

// Profile
$lang['WEBSITE_ERROR'] = '"Site" får endast innehålla http://sitename';
$lang['ICQ_ERROR'] = 'Området "ICQ" får endast innehålla icq nummer';
$lang['INVALID_DATE'] = 'Fel datum ';
$lang['PROFILE_USER'] = 'Visa profil';
$lang['GOOD_UPDATE'] = 'ändrats';
$lang['UCP_DOWNLOADS'] = 'Nedladdningar';
$lang['HIDE_DOWNLOADS'] = 'Dölja den aktuella listan över nedladdningar på din profil';
$lang['BAN_USER'] = 'För att förhindra en användare';
$lang['USER_NOT_ALLOWED'] = 'Användare är inte tillåtet';
$lang['HIDE_AVATARS'] = 'Visa avatarer';
$lang['SHOW_CAPTION'] = 'Visa din signatur';
$lang['DOWNLOAD_TORRENT'] = 'Ladda ner torrent';
$lang['SEND_PM'] = 'Skicka PM';
$lang['SEND_MESSAGE'] = 'Skicka meddelande';
$lang['NEW_THREADS'] = 'Nya Trådar';
$lang['PROFILE_NOT_FOUND'] = 'Profil inte hittas';

$lang['USER_DELETE'] = 'Ta bort';
$lang['USER_DELETE_EXPLAIN'] = 'Ta bort den här användaren';
$lang['USER_DELETE_ME'] = 'Ledsen, ditt konto är förbjudet att ta bort!';
$lang['USER_DELETE_CSV'] = 'Sorry, detta konto är inte tillåtet att ta bort!';
$lang['USER_DELETE_CONFIRM'] = 'Är du säker på att du vill ta bort den här användaren?';
$lang['USER_DELETED'] = 'Användaren raderades';
$lang['DELETE_USER_ALL_POSTS'] = 'Ta bort alla användares inlägg';
$lang['DELETE_USER_ALL_POSTS_CONFIRM'] = 'Är du säker på att du vill ta bort alla meddelanden och alla ämnen startade av denna användare?';
$lang['DELETE_USER_POSTS'] = 'Radera alla meddelanden, med undantag för inkomst';
$lang['DELETE_USER_POSTS_ME'] = 'Är du säker på att du vill ta bort alla mina inlägg?';
$lang['DELETE_USER_POSTS_CONFIRM'] = 'Är du säker på att du vill radera alla meddelanden, med undantag för inkomst?';
$lang['USER_DELETED_POSTS'] = 'Inlägg var framgångsrikt bort';

$lang['USER'] = 'Användare';
$lang['ROLE'] = 'Roll:';
$lang['MEMBERSHIP_IN'] = 'Medlemskap i';
$lang['PARTY'] = 'Fest:';
$lang['CANDIDATE'] = 'Kandidat:';
$lang['INDIVIDUAL'] = 'Har den enskilda rättigheter';
$lang['GROUP_LIST_HIDDEN'] = 'Du har inte behörighet att visa dolda grupper';

$lang['USER_ACTIVATE'] = 'Aktivera';
$lang['USER_DEACTIVATE'] = 'Inaktivera';
$lang['DEACTIVATE_CONFIRM'] = 'Är du säker på att du vill aktivera den här användaren?';
$lang['USER_ACTIVATE_ON'] = 'Användaren har aktiverats';
$lang['USER_DEACTIVATE_ME'] = 'Du kan inte inaktivera mitt konto!';
$lang['ACTIVATE_CONFIRM'] = 'Är du säker på att du vill inaktivera den här användaren?';
$lang['USER_ACTIVATE_OFF'] = 'Användaren har inaktiverats';

// Register
$lang['CHOOSE_A_NAME'] = 'Du bör välja ett namn';
$lang['CHOOSE_E_MAIL'] = 'Du måste ange e-post';
$lang['CHOOSE_PASS'] = 'Fältet för lösenord får inte vara tomt!';
$lang['CHOOSE_PASS_ERR'] = 'In lösenorden matchar inte';
$lang['CHOOSE_PASS_ERR_MIN'] = 'Ditt lösenord måste vara minst %d tecken';
$lang['CHOOSE_PASS_ERR_MAX'] = 'Ditt angivna lösenord får inte vara längre än $d tecken';
$lang['CHOOSE_PASS_ERR_NUM'] = 'The password must contain at least one digit';
$lang['CHOOSE_PASS_ERR_LETTER'] = 'The password must contain at least one letter of the Latin alphabet';
$lang['CHOOSE_PASS_ERR_LETTER_UPPERCASE'] = 'The password must contain at least one uppercase letter of the Latin alphabet';
$lang['CHOOSE_PASS_ERR_SPEC_SYMBOL'] = 'The password must contain at least one special character';
$lang['CHOOSE_PASS_OK'] = 'Lösenord match';
$lang['CHOOSE_PASS_REG_OK'] = 'Lösenord match, kan du gå vidare med registrering';
$lang['CHOOSE_PASS_FAILED'] = 'För att byta lösenord måste du ange korrekt lösenord';
$lang['EMAILER_DISABLED'] = 'Tyvärr är denna funktion inte fungerar tillfälligt';
$lang['TERMS_ON'] = 'Jag håller med dessa termer och villkor';
$lang['TERMS_OFF'] = 'Jag håller inte med om att dessa villkor';
$lang['JAVASCRIPT_ON_REGISTER'] = 'För att registrera dig, heads som är nödvändiga för att aktivera JavaScript';
$lang['REGISTERED_IN_TIME'] = "Just nu registrering är closed<br /><br />You kan registrera dig från 01:00 till 17:00 MSK (nu " . date('H:i') . " MSK)<br /><br />We ber om ursäkt för detta";
$lang['AUTOCOMPLETE'] = 'Generera lösenord';
$lang['YOUR_NEW_PASSWORD'] = 'Ditt nya lösenord:';
$lang['REGENERATE'] = 'Förnya';

// Debug
$lang['EXECUTION_TIME'] = 'Tid för genomförandet:';
$lang['SEC'] = 'sek';
$lang['ON'] = 'på';
$lang['OFF'] = 'mindre';
$lang['MEMORY'] = 'Mem: ';
$lang['QUERIES'] = 'frågor';
$lang['LIMIT'] = 'Gräns:';
$lang['SHOW_LOG'] = 'Show log';
$lang['EXPLAINED_LOG'] = 'Explained log';
$lang['CUT_LOG'] = 'Cut long queries';

// Attach Guest
$lang['DOWNLOAD_INFO'] = 'Ladda ner gratis på högsta hastighet!';
$lang['HOW_TO_DOWNLOAD'] = 'Hur laddar man Ner?';
$lang['WHAT_IS_A_TORRENT'] = 'Vad är en torrent?';
$lang['RATINGS_AND_LIMITATIONS'] = 'Betyg och Begränsningar';

$lang['SCREENSHOTS_RULES'] = 'Läs reglerna skärmdumpar!';
$lang['SCREENSHOTS_RULES_TOPIC'] = 'Läs reglerna skärmbilderna i det här avsnittet!';
$lang['AJAX_EDIT_OPEN'] = 'Har du redan öppnat en snabb redigering!';
$lang['GO_TO_PAGE'] = 'Gå till sidan ...';
$lang['EDIT'] = 'Ändra';
$lang['SAVE'] = 'Spara';
$lang['NEW_WINDOW'] = 'i ett nytt fönster';

// BB Code
$lang['ALIGN'] = 'Align:';
$lang['LEFT'] = 'Till vänster';
$lang['RIGHT'] = 'Till höger';
$lang['CENTER'] = 'Centrerad';
$lang['JUSTIFY'] = 'Anpassa till bredd';
$lang['HOR_LINE'] = 'Horisontell linje (Ctrl+8)';
$lang['NEW_LINE'] = 'Ny linje';
$lang['BOLD'] = 'Fet text: [b]text[/b] (Ctrl+B)';
$lang['ITALIC'] = 'Kursiv text: [i]text[/i] (Ctrl+I)';
$lang['UNDERLINE'] = 'Understruken text: [u]text[/u] (Ctrl+U)';
$lang['STRIKEOUT'] = 'Genomstruken text: [s]text[/s] (Ctrl+S)';
$lang['BOX_TAG'] = 'Frame around text: [box]text[/box] or [box=#333,#888]text[/box]';
$lang['INDENT_TAG'] = 'Insert indent: [indent]text[/indent]';
$lang['PRE_TAG'] = 'Preformatted text: [pre]text[/pre]';
$lang['NFO_TAG'] = 'NFO: [nfo]text[/nfo]';
$lang['SUPERSCRIPT'] = 'Superscript text: [sup]text[/sup]';
$lang['SUBSCRIPT'] = 'Subscript text: [sub]text[/sub]';
$lang['QUOTE_TITLE'] = 'Citera text: [quote]text[/quote] (Ctrl+Q)';
$lang['IMG_TITLE'] = 'Infoga bild: [img]https://image_url[/img] (Ctrl+R)';
$lang['URL'] = 'Url';
$lang['URL_TITLE'] = 'Infoga URL: [url]https://url[/url] eller [url=https://url]URL text[/url] (Ctrl+W)';
$lang['CODE_TITLE'] = 'Kod display: [code]code[/code] (Ctrl+K)';
$lang['LIST'] = 'Listan';
$lang['LIST_TITLE'] = 'Lista: [list]text[/list] (Ctrl+l)';
$lang['LIST_ITEM'] = 'Numrerad lista: [list=]text[/list] (Ctrl+O)';
$lang['ACRONYM'] = 'Acronym';
$lang['ACRONYM_TITLE'] = 'Acronym: [acronym=Full text]Short text[/acronym]';
$lang['QUOTE_SEL'] = 'Utvalda citat';
$lang['JAVASCRIPT_ON'] = 'Huvuden som är nödvändiga för att skicka meddelanden för att aktivera JavaScript';

$lang['NEW'] = 'Nya';
$lang['NEWEST'] = 'Nyaste';
$lang['LATEST'] = 'Senaste';
$lang['POST'] = 'Inlägg';
$lang['OLD'] = 'Gamla';

// DL-List
$lang['DL_USER'] = 'Användarnamn';
$lang['DL_PERCENT'] = 'Komplett procent';
$lang['DL_UL'] = 'UL';
$lang['DL_DL'] = 'DL';
$lang['DL_UL_SPEED'] = 'UL hastighet';
$lang['DL_DL_SPEED'] = 'DL hastighet';
$lang['DL_PORT'] = 'Port';
$lang['DL_CLIENT'] = 'BitTorrent client';
$lang['DL_FORMULA'] = 'Formel: Upp/TorrentSize';
$lang['DL_ULR'] = 'ULR';
$lang['DL_STOPPED'] = 'slutat';
$lang['DL_UPD'] = 'upd: ';
$lang['DL_INFO'] = 'visar data <i><b>only för den aktuella session</b></i>';
$lang['HIDE_PEER_TORRENT_CLIENT'] = 'Hide my BitTorrent client name in peer list';
$lang['HIDE_PEER_COUNTRY_NAME'] = 'Hide my country name in peer list';
$lang['HIDE_PEER_USERNAME'] = 'Hide my username in peer list';

// Post PIN
$lang['POST_PIN'] = 'Pin-första inlägget';
$lang['POST_UNPIN'] = 'Lossa första inlägget';
$lang['POST_PINNED'] = 'Första inlägget nålas';
$lang['POST_UNPINNED'] = 'Första inlägget unpinned';

// Management of my messages
$lang['GOTO_MY_MESSAGE'] = 'Stäng och gå tillbaka till listan "Mina Meddelanden"';
$lang['DEL_MY_MESSAGE'] = 'Utvalda ämnen har tagits bort från "Mina Meddelanden"';
$lang['NO_TOPICS_MY_MESSAGE'] = 'Inga ämnen som finns i listan för ditt inlägg (kanske har du redan tagit bort dem)';
$lang['EDIT_MY_MESSAGE_LIST'] = 'redigera lista';
$lang['SELECT_INVERT'] = 'select / invert';
$lang['RESTORE_ALL_POSTS'] = 'Återställa alla inlägg';
$lang['DEL_LIST_MY_MESSAGE'] = 'Ta bort den valda ämne från listan';
$lang['DEL_LIST_MY_MESSAGE_INFO'] = 'Efter avlägsnande av upp till uppdatera <b>entire list</b> det kan påvisas redan raderade trådar';
$lang['DEL_LIST_INFO'] = 'För att ta bort en order från listan, klicka på ikonen till vänster om namnen på alla avsnitt';

// Watched topics
$lang['WATCHED_TOPICS'] = 'Såg ämnen';
$lang['NO_WATCHED_TOPICS'] = 'No watching any topics';

// set_die_append_msg
$lang['INDEX_RETURN'] = 'Tillbaka till startsidan';
$lang['FORUM_RETURN'] = 'Tillbaka till forumet';
$lang['TOPIC_RETURN'] = 'Tillbaka till ämnet';
$lang['POST_RETURN'] = 'Gå till inlägget';
$lang['PROFILE_EDIT_RETURN'] = 'Återgå till redigering';
$lang['PROFILE_RETURN'] = 'Gå till profil';

$lang['WARNING'] = 'Varning';
$lang['INDEXER'] = 'Reindex sök';

$lang['FORUM_STYLE'] = 'Forum stil';

$lang['LINKS_ARE_FORBIDDEN'] = 'Länkar är förbjudet';

$lang['GENERAL'] = 'Allmänt Admin';
$lang['USERS'] = 'Användaren Admin';
$lang['GROUPS'] = 'Gruppen Admin';
$lang['FORUMS'] = 'Forum Admin';
$lang['MODS'] = 'Ändringar';

$lang['CONFIGURATION'] = 'Konfiguration';
$lang['MANAGE'] = 'Förvaltning';
$lang['DISALLOW'] = 'Disallow namn';
$lang['PRUNE'] = 'Beskärning';
$lang['MASS_EMAIL'] = 'Massutskick Av E-Post';
$lang['RANKS'] = 'Leden';
$lang['SMILIES'] = 'Smilies';
$lang['BAN_MANAGEMENT'] = 'Förbud-Kontroll';
$lang['WORD_CENSOR'] = 'Ordet Censorerna';
$lang['EXPORT'] = 'Export';
$lang['CREATE_NEW'] = 'Skapa';
$lang['ADD_NEW'] = 'Lägg till';
$lang['CRON'] = 'Schemaläggaren (cron)';
$lang['REBUILD_SEARCH_INDEX'] = 'Återskapa sökindex';
$lang['FORUM_CONFIG'] = 'Forum inställningar';
$lang['TRACKER_CONFIG'] = 'Tracker-inställningar';
$lang['RELEASE_TEMPLATES'] = 'Släpp Mallar';
$lang['ACTIONS_LOG'] = 'Rapport om åtgärder';

// Migrations
$lang['MIGRATIONS_STATUS']  = 'Database Migration Status';
$lang['MIGRATIONS_DATABASE_NAME']  = 'Database Name';
$lang['MIGRATIONS_DATABASE_TOTAL']  = 'Total Tables';
$lang['MIGRATIONS_DATABASE_SIZE']  = 'Database Size';
$lang['MIGRATIONS_DATABASE_INFO']  = 'Database Information';
$lang['MIGRATIONS_SYSTEM']  = 'Migration System';
$lang['MIGRATIONS_NEEDS_SETUP']  = 'Needs Setup';
$lang['MIGRATIONS_ACTIVE']  = 'Aktiv';
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
$lang['MAIN_INDEX'] = 'Forum Index';
$lang['FORUM_STATS'] = 'Forum-Statistik';
$lang['ADMIN_INDEX'] = 'Admin Index';
$lang['CREATE_PROFILE'] = 'Skapa profil';

$lang['TP_VERSION'] = 'TorrentPier version';
$lang['TP_RELEASE_DATE'] = 'Utgivningsdatum';
$lang['PHP_INFO'] = 'Information om PHP';

$lang['CLICK_RETURN_ADMIN_INDEX'] = 'Klicka %sHere%s för att återgå till Index Admin';

$lang['NUMBER_POSTS'] = 'Antal inlägg';
$lang['POSTS_PER_DAY'] = 'Inlägg per dag';
$lang['NUMBER_TOPICS'] = 'Antal ämnen';
$lang['TOPICS_PER_DAY'] = 'Ämnen per dag';
$lang['NUMBER_USERS'] = 'Antal användare';
$lang['USERS_PER_DAY'] = 'Användare per dag';
$lang['BOARD_STARTED'] = 'Styrelsen började';
$lang['AVATAR_DIR_SIZE'] = 'Avatar katalog storlek';
$lang['DATABASE_SIZE'] = 'Databasens storlek';
$lang['GZIP_COMPRESSION'] = 'Gzip-komprimering';
$lang['NOT_AVAILABLE'] = 'Inte tillgänglig';

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
$lang['USER_LEVELS'] = 'Användarnivåer';
$lang['USER_LEVELS_UPDATED'] = 'Användarnivåer har uppdaterats';

// Synchronize
$lang['SYNCHRONIZE'] = 'Synkronisera';
$lang['TOPICS_DATA_SYNCHRONIZED'] = 'Ämnen som data har synkroniserats';
$lang['USER_POSTS_COUNT'] = 'Användaren inlägg räkna';
$lang['USER_POSTS_COUNT_SYNCHRONIZED'] = 'Användaren inlägg räkna har synkroniserats';

// Online Userlist
$lang['SHOW_ONLINE_USERLIST'] = 'Visa listan över användare online';

// Robots.txt editor
$lang['ROBOTS_TXT_EDITOR_TITLE'] = 'Manage robots.txt';
$lang['ROBOTS_TXT_UPDATED_SUCCESSFULLY'] = 'File robots.txt has been updated successfully';
$lang['CLICK_RETURN_ROBOTS_TXT_CONFIG'] = '%sClick Here to return to robots.txt manager%s';

// Auth pages
$lang['USER_SELECT'] = 'Välj ett Användarnamn';
$lang['GROUP_SELECT'] = 'Välj en Grupp';
$lang['SELECT_A_FORUM'] = 'Välj ett Forum';
$lang['AUTH_CONTROL_USER'] = 'Användarbehörigheter Kontroll';
$lang['AUTH_CONTROL_GROUP'] = 'Grupp Behörigheter Kontroll';
$lang['AUTH_CONTROL_FORUM'] = 'Forum Behörigheter Kontroll';
$lang['LOOK_UP_FORUM'] = 'Leta upp Forum';

$lang['GROUP_AUTH_EXPLAIN'] = 'Här kan du ändra behörigheter och moderator status som tilldelas varje grupp. Glöm inte när du byter grupp behörigheter för enskilda användare behörigheter kan fortfarande tillåter användaren inlägg på forum, etc. Du kommer att varnas om detta är fallet.';
$lang['USER_AUTH_EXPLAIN'] = 'Här kan du ändra behörigheter och moderator status som tilldelas varje enskild användare. Glöm inte när du byter användare behörigheter som gruppen behörigheter kan fortfarande tillåter användaren inlägg på forum, etc. Du kommer att varnas om detta är fallet.';
$lang['FORUM_AUTH_EXPLAIN'] = 'Här kan du ändra behörighetsnivåer i varje forum. Du kommer att ha både en enkel och en avancerad metod för att göra detta, där avancerade ger större kontroll på varje forum drift. Kom ihåg att ändra behörighetsnivå för forum kommer att påverka vilka användare som kan utföra olika åtgärder på dem.';

$lang['SIMPLE_MODE'] = 'Enkelt Läge';
$lang['ADVANCED_MODE'] = 'Avancerat Läge';
$lang['MODERATOR_STATUS'] = 'Moderator status';

$lang['ALLOWED_ACCESS'] = 'Tillträde';
$lang['DISALLOWED_ACCESS'] = 'Otillåten Åtkomst';
$lang['IS_MODERATOR'] = 'Är Moderator';

$lang['CONFLICT_WARNING'] = 'Tillstånd Konflikt Varning';
$lang['CONFLICT_ACCESS_USERAUTH'] = 'Den här användaren har fortfarande åtkomst rättigheter till detta forum via medlemskap i gruppen. Du kanske vill ändra behörigheter eller ta bort den här användaren grupp för att helt förhindra dem att ha tillgång till rättigheter. De grupper som ger rättigheter (och forum inblandade) är noterat nedan.';
$lang['CONFLICT_MOD_USERAUTH'] = 'Den här användaren har fortfarande moderator rättigheter till detta forum via medlemskap i gruppen. Du kanske vill ändra behörigheter eller ta bort den här användaren grupp för att helt förhindra dem med moderator rättigheter. De grupper som ger rättigheter (och forum inblandade) är noterat nedan.';

$lang['CONFLICT_ACCESS_GROUPAUTH'] = 'Följande användare (eller användare) fortfarande har åtkomst rättigheter till detta forum via sina användare behörighet inställningar. Du kanske vill ändra behörigheter för att helt förhindra dem att ha tillgång till rättigheter. De användare som har beviljats rättigheter (och forum inblandade) är noterat nedan.';
$lang['CONFLICT_MOD_GROUPAUTH'] = 'Följande användare (eller användare) har fortfarande moderator rättigheter till detta forum via sina användare behörigheter inställningar. Du kanske vill ändra behörigheter för att helt förhindra dem med moderator rättigheter. De användare som har beviljats rättigheter (och forum inblandade) är noterat nedan.';

$lang['PUBLIC'] = 'Allmänna';
$lang['PRIVATE'] = 'Privat';
$lang['REGISTERED'] = 'Registrerade';
$lang['ADMINISTRATORS'] = 'Administratörer';

// These are displayed in the drop-down boxes for advanced mode forum auth, try and keep them short!
$lang['FORUM_ALL'] = 'ALLA';
$lang['FORUM_REG'] = 'REG';
$lang['FORUM_PRIVATE'] = 'PRIVAT';
$lang['FORUM_MOD'] = 'MOD';
$lang['FORUM_ADMIN'] = 'ADMIN';

$lang['AUTH_VIEW'] = $lang['VIEW'] = 'Visa';
$lang['AUTH_READ'] = $lang['READ'] = 'Läs';
$lang['AUTH_POST'] = $lang['POST'] = 'Inlägg';
$lang['AUTH_REPLY'] = $lang['REPLY'] = 'Svara';
$lang['AUTH_EDIT'] = $lang['EDIT'] = 'Ändra';
$lang['AUTH_DELETE'] = $lang['DELETE'] = 'Ta bort';
$lang['AUTH_STICKY'] = $lang['STICKY'] = 'Sticky';
$lang['AUTH_ANNOUNCE'] = $lang['ANNOUNCE'] = 'Meddela';
$lang['AUTH_VOTE'] = $lang['VOTE'] = 'Omröstning';
$lang['AUTH_POLLCREATE'] = $lang['POLLCREATE'] = 'Skapa enkät';
$lang['AUTH_ATTACHMENTS'] = $lang['AUTH_ATTACH'] = 'Skicka Filer';
$lang['AUTH_DOWNLOAD'] = 'Ladda Ner Filer';

$lang['SIMPLE_PERMISSION'] = 'Enkel Behörigheter';

$lang['USER_LEVEL'] = 'Användar Nivå';
$lang['AUTH_USER'] = 'Användare';
$lang['AUTH_ADMIN'] = 'Administratör';
$lang['GROUP_MEMBERSHIPS'] = 'Användargrupp medlemskap';
$lang['USERGROUP_MEMBERS'] = 'Den här gruppen har följande medlemmar';

$lang['FORUM_AUTH_UPDATED'] = 'Forum behörigheter har uppdaterats';
$lang['USER_AUTH_UPDATED'] = 'Användarbehörigheter har uppdaterats';
$lang['GROUP_AUTH_UPDATED'] = 'Behörigheter grupp har uppdaterats';

$lang['AUTH_UPDATED'] = 'Behörigheter har uppdaterats';
$lang['AUTH_GENERAL_ERROR'] = 'Could not update admin status';
$lang['AUTH_SELF_ERROR'] = 'Could not change yourself from an admin to user';
$lang['CLICK_RETURN_USERAUTH'] = 'Klicka %sHere%s för att återgå till Användare Behörigheter';
$lang['CLICK_RETURN_GROUPAUTH'] = 'Klicka %sHere%s för att återgå till Grupp Behörigheter';
$lang['CLICK_RETURN_FORUMAUTH'] = 'Klicka %sHere%s för att återvända till Forumet Behörigheter';

// Banning
$lang['BAN_CONTROL'] = 'Förbud-Kontroll';
$lang['BAN_EXPLAIN'] = 'Here you can control the banning of users.';
$lang['BAN_USERNAME'] = 'Förbjuda en eller flera specifika användare';
$lang['BAN_USERNAME_EXPLAIN'] = 'Du kan stänga av flera användare på en gång genom att använda lämplig kombination av mus och tangentbord till din dator och webbläsare';
$lang['UNBAN_USERNAME'] = 'Unban one more specific users';
$lang['UNBAN_USERNAME_EXPLAIN'] = 'Du kan unban flera användare på en gång genom att använda lämplig kombination av mus och tangentbord till din dator och webbläsare';
$lang['NO_BANNED_USERS'] = 'Ingen förbjudit användarnamn';
$lang['BAN_UPDATE_SUCESSFUL'] = 'Bannlysningslistan har uppdaterats';
$lang['CLICK_RETURN_BANADMIN'] = 'Klicka %sHere%s för att återgå till Ban Kontroll';

// Configuration
$lang['GENERAL_CONFIG'] = 'Allmän Konfiguration';
$lang['CONFIG_EXPLAIN'] = 'I formuläret nedan kan du anpassa alla styrelsen alternativ. För Användaren och Forum konfigurationer använda relaterade länkar på vänster sida.';

$lang['CONFIG_MODS'] = 'Konfiguration ändringar';
$lang['MODS_EXPLAIN'] = 'I detta formulär kan du justera ändringar';

$lang['CLICK_RETURN_CONFIG'] = '%sClick Här för att återvända till Allmänna Configuration%s';
$lang['CLICK_RETURN_CONFIG_MODS'] = '%sBack till inställningar modifications%s';

$lang['GENERAL_SETTINGS'] = 'Styrelsen Inställningar';
$lang['SITE_NAME'] = 'Webbplatsens namn';
$lang['SITE_DESC'] = 'Webbplatsen beskrivning';
$lang['FORUMS_DISABLE'] = 'Inaktivera styrelsen';
$lang['BOARD_DISABLE_EXPLAIN'] = 'Detta kommer att göra att styrelsen inte tillgänglig för användare. Administratörer har åtkomst till Administration Panel medan styrelsen som är funktionshindrade.';
$lang['ACCT_ACTIVATION'] = 'Aktivera konto aktivering';
$lang['ACC_NONE'] = 'Ingen'; // These three entries are the type of activation
$lang['ACC_USER'] = 'Användare';
$lang['ACC_ADMIN'] = 'Admin';

$lang['ABILITIES_SETTINGS'] = 'Användaren och Forum Grundläggande Inställningar';
$lang['MAX_POLL_OPTIONS'] = 'Max antal omröstningsalternativ';
$lang['FLOOD_INTERVAL'] = 'Översvämning Intervall';
$lang['FLOOD_INTERVAL_EXPLAIN'] = 'Antalet sekunder som en användare måste vänta mellan inlägg';
$lang['TOPICS_PER_PAGE'] = 'Ämnen Per Sida';
$lang['POSTS_PER_PAGE'] = 'Inlägg Per Sida';
$lang['HOT_THRESHOLD'] = 'Tjänster för Populära Tröskeln';
$lang['DEFAULT_LANGUAGE'] = 'Standardspråk';
$lang['DATE_FORMAT'] = 'Datum Format';
$lang['SYSTEM_TIMEZONE'] = 'Systemet Tidszon';
$lang['ENABLE_PRUNE'] = 'Aktivera Forum Beskärning';
$lang['ALLOW_BBCODE'] = 'Låt BBCode';
$lang['ALLOW_SMILIES'] = 'Tillåt Smilies';
$lang['SMILIES_PATH'] = 'Smilies Lagring Väg';
$lang['SMILIES_PATH_EXPLAIN'] = 'Sökväg under din TorrentPier root dir, t ex stilar/images/smiles';
$lang['ALLOW_SIG'] = 'Låt Signaturer';
$lang['MAX_SIG_LENGTH'] = 'Maximal signatur längd';
$lang['MAX_SIG_LENGTH_EXPLAIN'] = 'Maximalt antal tecken i användarens signaturer';
$lang['ALLOW_NAME_CHANGE'] = 'Låt Användarnamn förändringar';

$lang['EMAIL_SETTINGS'] = 'E-Post Inställningar';

// Visual Confirmation
$lang['VISUAL_CONFIRM'] = 'Aktivera Visuell Bekräftelse';
$lang['VISUAL_CONFIRM_EXPLAIN'] = 'Kräver att användarna anger en kod som definieras av en bild när du registrerar dig.';

// Autologin Keys
$lang['ALLOW_AUTOLOGIN'] = 'Tillåt automatiska inloggningar';
$lang['ALLOW_AUTOLOGIN_EXPLAIN'] = 'Avgör om användarna får välja att automatiskt loggas in när du besöker forumet';
$lang['AUTOLOGIN_TIME'] = 'Automatisk login nyckel utgången';
$lang['AUTOLOGIN_TIME_EXPLAIN'] = 'Hur länge en autologin nyckel är giltig för i dagar om användaren inte besöka styrelsen. Satt till noll för att inaktivera utgången.';

// Forum Management
$lang['FORUM_ADMIN_MAIN'] = 'Forum Administration';
$lang['FORUM_ADMIN_EXPLAIN'] = 'Från denna instans kan du lägga till, ta bort, redigera, ändra ordning och re-synkronisera kategorier och forum';
$lang['EDIT_FORUM'] = 'Redigera forum';
$lang['CREATE_FORUM'] = 'Skapa nytt forum';
$lang['CREATE_SUB_FORUM'] = 'Create subforum';
$lang['CREATE_CATEGORY'] = 'Skapa ny kategori';
$lang['REMOVE'] = 'Ta bort';
$lang['UPDATE_ORDER'] = 'Uppdatera För';
$lang['CONFIG_UPDATED'] = 'Forum Konfiguration Har Uppdaterats';
$lang['MOVE_UP'] = 'Flytta upp';
$lang['MOVE_DOWN'] = 'Flytta ned';
$lang['RESYNC'] = 'Resync';
$lang['NO_MODE'] = 'Ingen läget var inställt';
$lang['FORUM_EDIT_DELETE_EXPLAIN'] = 'I formuläret nedan kan du anpassa alla styrelsen alternativ. För Användaren och Forum konfigurationer använda relaterade länkar på vänster sida';

$lang['MOVE_CONTENTS'] = 'Flytta allt innehåll';
$lang['FORUM_DELETE'] = 'Ta Bort Forum';
$lang['FORUM_DELETE_EXPLAIN'] = 'I formuläret nedan kan du ta bort ett forum (eller kategori) och bestäm var du vill placera alla ämnen (och forum) det innehöll.';
$lang['CATEGORY_DELETE'] = 'Ta Bort Kategori';
$lang['CATEGORY_NAME_EMPTY'] = 'Category name not specified';

$lang['STATUS_LOCKED'] = 'Låst';
$lang['STATUS_UNLOCKED'] = 'Olåst';
$lang['FORUM_SETTINGS'] = 'Allmänt Forum Inställningar';
$lang['FORUM_NAME'] = 'Forum namn';
$lang['FORUM_DESC'] = 'Beskrivning';
$lang['FORUM_STATUS'] = 'Forum status';
$lang['FORUM_PRUNING'] = 'Auto-beskärning';

$lang['PRUNE_DAYS'] = 'Ta bort ämnen som inte har bokförts i';
$lang['SET_PRUNE_DATA'] = 'Du har vänt på auto-beskära för detta forum, men inte fastställa ett antal dagar för att beskära. Vänligen gå tillbaka och göra så.';

$lang['MOVE_AND_DELETE'] = 'Flytta och ta Bort';

$lang['DELETE_ALL_POSTS'] = 'Ta bort alla inlägg';
$lang['DELETE_ALL_TOPICS'] = 'Ta bort alla ämnen, inklusive meddelanden och klibbig';
$lang['NOWHERE_TO_MOVE'] = 'Ingenstans att flytta till';

$lang['EDIT_CATEGORY'] = 'Redigera Kategori';
$lang['EDIT_CATEGORY_EXPLAIN'] = 'Använd detta formulär för att ändra en kategori namn.';

$lang['FORUMS_UPDATED'] = 'Forum och information om Kategori uppdaterats';

$lang['MUST_DELETE_FORUMS'] = 'Du måste ta bort alla forum innan du kan ta bort denna kategori';

$lang['CLICK_RETURN_FORUMADMIN'] = 'Klicka %sHere%s för att återvända till Forumet Administration';

$lang['SHOW_ALL_FORUMS_ON_ONE_PAGE'] = 'Visa alla forum på en sida';

// Smiley Management
$lang['SMILEY_TITLE'] = 'Ler Du Redigerar Utility';
$lang['SMILE_DESC'] = 'Från denna sida kan du lägga till, ta bort och redigera emoticons eller smileys som dina användare kan använda i sina inlägg och privata meddelanden.';

$lang['SMILEY_CONFIG'] = 'Smiley Konfiguration';
$lang['SMILEY_CODE'] = 'Smiley-Kod';
$lang['SMILEY_URL'] = 'Smiley Bildfil';
$lang['SMILEY_EMOT'] = 'Smiley Känslor';
$lang['SMILE_ADD'] = 'Lägga till en ny Smiley';
$lang['SMILE'] = 'Leende';
$lang['EMOTION'] = 'Känslor';

$lang['SELECT_PAK'] = 'Välj Pack (.pak-Fil) ';
$lang['REPLACE_EXISTING'] = 'Ersätta Befintliga Smiley';
$lang['KEEP_EXISTING'] = 'Behålla Befintliga Smiley';
$lang['SMILEY_IMPORT_INST'] = 'Du bör packa upp smiley paket och ladda upp alla filer till en lämplig Smiley-katalogen för din installation. Välj sedan rätt information i detta formulär för att importera smiley-pack.';
$lang['SMILEY_IMPORT'] = 'Smiley Pack Import';
$lang['CHOOSE_SMILE_PAK'] = 'Välj ett Leende Pack .pak-fil';
$lang['IMPORT'] = 'Importera Smileys';
$lang['SMILE_CONFLICTS'] = 'Vad som ska göras i händelse av konflikter';
$lang['DEL_EXISTING_SMILEYS'] = 'Ta bort befintliga smileys innan import';
$lang['IMPORT_SMILE_PACK'] = 'Importera Smiley-Pack';
$lang['EXPORT_SMILE_PACK'] = 'Skapa Smiley-Pack';
$lang['EXPORT_SMILES'] = 'För att skapa en smiley pack från dina installerade smileys, klicka %sHere%s för att hämta ler.pak-fil. Namnge den här filen på lämpligt sätt se till att hålla den .pak file extension. Skapa sedan en zip-fil som innehåller alla av din smiley bilder plus detta .pak konfigurationsfilen.';

$lang['SMILEY_ADD_SUCCESS'] = 'Smiley har lagts till framgång';
$lang['SMILEY_EDIT_SUCCESS'] = 'Smiley har uppdaterats';
$lang['SMILEY_IMPORT_SUCCESS'] = 'Smiley Pack har importerats framgångsrikt!';
$lang['SMILEY_DEL_SUCCESS'] = 'Smiley har tagits bort';
$lang['CLICK_RETURN_SMILEADMIN'] = 'Klicka %sHere%s för att återgå till Smiley Administration';

// User Management
$lang['USER_ADMIN'] = 'Användaradministration';
$lang['USER_ADMIN_EXPLAIN'] = 'Här kan du ändra dina användares information och vissa alternativ. För att ändra användarnas behörigheter, vänligen använd användare och behörigheter systemet.';

$lang['LOOK_UP_USER'] = 'Leta upp användaren';

$lang['ADMIN_USER_FAIL'] = 'Kunde inte uppdatera användarens profil.';
$lang['ADMIN_USER_UPDATED'] = 'Användarens profil har uppdaterats.';
$lang['CLICK_RETURN_USERADMIN'] = 'Klicka %sHere%s för att återgå till Användaren Administration';

$lang['USER_ALLOWPM'] = 'Kan skicka Privata Meddelanden';
$lang['USER_ALLOWAVATAR'] = 'Kan visa avatar';

$lang['ADMIN_AVATAR_EXPLAIN'] = 'Här kan du se och ta bort användarens nuvarande avatar.';

$lang['USER_SPECIAL'] = 'Särskilda admin fält';
$lang['USER_SPECIAL_EXPLAIN'] = 'Dessa fält kan inte ändras av användare. Här kan du ange deras status och andra alternativ som inte ges till användarna.';

// Group Management
$lang['GROUP_ADMINISTRATION'] = 'Koncernens Administration';
$lang['GROUP_ADMIN_EXPLAIN'] = 'Från denna instans kan du administrera alla dina användargrupper. Du kan ta bort, skapa och redigera befintliga grupper. Du kan välja moderatorer, växla öppet/stängt gruppen status och ange gruppnamn och beskrivning';
$lang['ERROR_UPDATING_GROUPS'] = 'Det uppstod ett fel vid uppdatering av grupper';
$lang['UPDATED_GROUP'] = 'Koncernen har uppdaterats';
$lang['ADDED_NEW_GROUP'] = 'Den nya koncernen har skapats';
$lang['DELETED_GROUP'] = 'Gruppen har raderats framgångsrikt';
$lang['CREATE_NEW_GROUP'] = 'Skapa ny grupp';
$lang['EDIT_GROUP'] = 'Redigera grupp';
$lang['GROUP_STATUS'] = 'Gruppen status';
$lang['GROUP_DELETE'] = 'Ta bort grupp';
$lang['GROUP_DELETE_CHECK'] = 'Ta bort den här gruppen';
$lang['SUBMIT_GROUP_CHANGES'] = 'Skicka In Förändringar';
$lang['RESET_GROUP_CHANGES'] = 'Återställ Ändringar';
$lang['NO_GROUP_NAME'] = 'Du måste ange ett namn för denna grupp';
$lang['NO_GROUP_MODERATOR'] = 'Du måste ange en moderator för denna grupp';
$lang['NO_GROUP_MODE'] = 'Du måste ange ett läge för denna grupp, som öppet eller stängt';
$lang['NO_GROUP_ACTION'] = 'Inga åtgärder var som anges';
$lang['DELETE_OLD_GROUP_MOD'] = 'Ta bort den gamla gruppen moderator?';
$lang['DELETE_OLD_GROUP_MOD_EXPL'] = 'Om du ändrar gruppen moderator, kryssa i denna ruta för att ta bort den gamla moderator från gruppen. Annars, inte kolla upp det, och användaren kommer att bli en vanlig medlem i gruppen.';
$lang['CLICK_RETURN_GROUPSADMIN'] = 'Klicka %sHere%s för att återgå till koncernadministrationen.';
$lang['SELECT_GROUP'] = 'Välj en grupp';
$lang['LOOK_UP_GROUP'] = 'Leta upp gruppen';

// Prune Administration
$lang['FORUM_PRUNE'] = 'Forum Beskära';
$lang['FORUM_PRUNE_EXPLAIN'] = 'Detta kommer att ta bort något ämne som inte har varit inlagd inom det antal dagar som du väljer. Om du inte anger ett nummer då alla ämnen kommer att tas bort. Det kommer inte att ta bort <b>sticky</b> ämnen och <b>announcements</b>. Du kommer att behöva för att ta bort dessa ämnen manuellt.';
$lang['DO_PRUNE'] = 'Gör Beskära';
$lang['ALL_FORUMS'] = 'Alla Forum';
$lang['PRUNE_TOPICS_NOT_POSTED'] = 'Beskära ämnen med inga svar på detta många dagar';
$lang['TOPICS_PRUNED'] = 'Ämnen beskäras';
$lang['POSTS_PRUNED'] = 'Inlägg beskäras';
$lang['PRUNE_SUCCESS'] = 'Forum har beskurit framgångsrikt';
$lang['NOT_DAYS'] = 'Beskära dagar inte valt';

// Word censor
$lang['WORDS_TITLE'] = 'Ord Att Censurera';
$lang['WORDS_EXPLAIN'] = 'Från kontrollpanelen kan du lägga till, redigera och ta bort ord som kommer att automatiskt bli censurerade på ditt forum. Dessutom kommer människor inte att vara tillåtet att registrera sig med användarnamn innehåller dessa ord. Jokertecken (*) godtas i fältet ord. Till exempel, *test* kommer att matcha avskyvärda, test* skulle matcha testning *testet skulle matcha avskyr.';
$lang['WORD'] = 'Ordet';
$lang['EDIT_WORD_CENSOR'] = 'Redigera word-censurera';
$lang['REPLACEMENT'] = 'Ersättare';
$lang['ADD_NEW_WORD'] = 'Lägga till nya ord';
$lang['UPDATE_WORD'] = 'Uppdatering ord censurera';

$lang['MUST_ENTER_WORD'] = 'Du måste skriva in ett ord och dess ersättare';
$lang['NO_WORD_SELECTED'] = 'Inga ord markerat för redigering';

$lang['WORD_UPDATED'] = 'Det valda ordet censurera har uppdaterats';
$lang['WORD_ADDED'] = 'Ordet censurera har lagts till framgång';
$lang['WORD_REMOVED'] = 'Det valda ordet censurera har tagits bort ';

$lang['CLICK_RETURN_WORDADMIN'] = 'Klicka %sHere%s för att återgå till Word Censurera Administration';

// Mass Email
$lang['MASS_EMAIL_EXPLAIN'] = 'Här kan du e-posta ett meddelande till antingen alla användare eller för alla användare i en viss grupp. För att göra detta, ett e-postmeddelande kommer att skickas ut till de administrativa e-postadress som tillhandahålls, med en kopia skickas till alla mottagare. Om du är e-post en stor grupp människor var tålmodig när du har skickat in och inte stoppa sida halvvägs igenom. Det är normalt för en massa e-post för att ta en lång tid och du kommer att meddelas när skriptet har avslutats';
$lang['COMPOSE'] = 'Komponera';

$lang['RECIPIENTS'] = 'Mottagare';
$lang['ALL_USERS'] = 'Alla Användare';

$lang['MASS_EMAIL_MESSAGE_TYPE'] = 'Typ av e-post';

$lang['EMAIL_SUCCESSFULL'] = 'Ditt meddelande har skickats';
$lang['CLICK_RETURN_MASSEMAIL'] = 'Klicka %sHere%s för att återgå till Massa E-form';

// Ranks admin
$lang['RANKS_TITLE'] = 'Rank Administration';
$lang['RANKS_EXPLAIN'] = 'Med hjälp av detta formulär kan du lägga till, redigera, visa och ta bort leden. Du kan också skapa egna leden som kan användas för att en användare via användaren anläggning för hantering av';

$lang['ADD_NEW_RANK'] = 'Lägg till en ny nivå';
$lang['RANK_TITLE'] = 'Rank Titel';
$lang['STYLE_COLOR'] = 'Stil rang';
$lang['STYLE_COLOR_FAQ'] = 'Ange klass för målning på titeln på önskad färg. Till exempel <i class="bold">colorAdmin<i>';
$lang['RANK_IMAGE'] = 'Rank Bild';
$lang['RANK_IMAGE_EXPLAIN'] = 'Använda denna för att definiera en liten bild som är associerat med rang';

$lang['MUST_SELECT_RANK'] = 'Du måste välja en ranking';
$lang['NO_ASSIGNED_RANK'] = 'Ingen speciell rangordning som tilldelats';

$lang['RANK_UPDATED'] = 'Rangen har uppdaterats';
$lang['RANK_ADDED'] = 'Rangen har lagts till framgång';
$lang['RANK_REMOVED'] = 'Rankningen har tagits bort';
$lang['NO_UPDATE_RANKS'] = 'Rankningen har tagits bort. Men, användarkonton med hjälp av denna rang var inte uppdaterad. Du kommer att behöva att manuellt nollställa ranken på dessa konton';

$lang['CLICK_RETURN_RANKADMIN'] = 'Klicka %sHere%s för att återgå till Rank Administration';

// Disallow Username Admin
$lang['DISALLOW_CONTROL'] = 'Användarnamn Inte Tillåta Kontroll';
$lang['DISALLOW_EXPLAIN'] = 'Här kan du kontrollera användarnamn som inte kommer att tillåtas att användas. Otillåten användarnamn är tillåtet att innehålla jokertecken *. Observera att du inte kommer att tillåtas att ange ett användarnamn som redan har registrerats. Du måste först ta bort det namnet sedan förkasta det.';

$lang['DELETE_DISALLOW'] = 'Ta bort';
$lang['DELETE_DISALLOW_TITLE'] = 'Ta bort en Otillåten Användarnamn';
$lang['DELETE_DISALLOW_EXPLAIN'] = 'Du kan ta bort en otillåten användarnamn genom att välja användarnamn från listan och klickar på skicka';

$lang['ADD_DISALLOW'] = 'Lägg till';
$lang['ADD_DISALLOW_TITLE'] = 'Lägg till en otillåten användarnamn';

$lang['NO_DISALLOWED'] = 'Inga Otillåtna Användarnamn';

$lang['DISALLOWED_DELETED'] = 'Det förbjudit användarnamn har tagits bort';
$lang['DISALLOW_SUCCESSFUL'] = 'Det förbjudit användarnamn har lagts till framgång';
$lang['DISALLOWED_ALREADY'] = 'Det namn du angett kan inte vara otillåten. Det antingen redan finns i listan, finns i ordet censurera lista, eller en kombination av användarnamn är närvarande.';

$lang['CLICK_RETURN_DISALLOWADMIN'] = 'Klicka %sHere%s att återvända för att ta bort Användarnamn Administration';

// Version Check
$lang['VERSION_INFORMATION'] = 'Version Information';
$lang['UPDATE_AVAILABLE'] = 'Update available';
$lang['CHANGELOG'] = 'Changelog';

// Login attempts configuration
$lang['MAX_LOGIN_ATTEMPTS'] = 'Tillåtna inloggningsförsök';
$lang['MAX_LOGIN_ATTEMPTS_EXPLAIN'] = 'Antalet tillåtna styrelsen inloggningsförsök.';
$lang['LOGIN_RESET_TIME'] = 'Logga in lås tid';
$lang['LOGIN_RESET_TIME_EXPLAIN'] = 'Tid i minuter användaren måste vänta tills han är tillåtna att logga in igen efter att ha överskridit antalet tillåtna inloggningsförsök.';

// Permissions List
$lang['PERMISSIONS_LIST'] = 'Behörigheter Lista';
$lang['AUTH_CONTROL_CATEGORY'] = 'Kategori Behörigheter Kontroll';
$lang['FORUM_AUTH_LIST_EXPLAIN'] = 'Detta ger en sammanfattning av tillståndet nivåer i varje forum. Du kan redigera dessa behörigheter med hjälp av antingen en enkel eller en avancerad metod genom att klicka på forum namn. Kom ihåg att ändra behörighetsnivå för forum kommer att påverka vilka användare som kan utföra olika åtgärder på dem.';
$lang['CAT_AUTH_LIST_EXPLAIN'] = 'Detta ger en sammanfattning av tillståndet nivåer i varje forum inom denna kategori. Du kan redigera behörigheter för enskilda forum, med antingen en enkel eller en avancerad metod genom att klicka på forum namn. Alternativt, kan du ställa in behörigheter för alla de forum i denna kategori genom att använda rullgardinsmenyerna längst ner på sidan. Kom ihåg att ändra behörighetsnivå för forum kommer att påverka vilka användare som kan utföra olika åtgärder på dem.';
$lang['FORUM_AUTH_LIST_EXPLAIN_ALL'] = 'Alla användare';
$lang['FORUM_AUTH_LIST_EXPLAIN_REG'] = 'Alla registrerade användare';
$lang['FORUM_AUTH_LIST_EXPLAIN_PRIVATE'] = 'Endast användare som har beviljats särskilt tillstånd';
$lang['FORUM_AUTH_LIST_EXPLAIN_MOD'] = 'Endast moderatorer för detta forum';
$lang['FORUM_AUTH_LIST_EXPLAIN_ADMIN'] = 'Endast administratörer';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_VIEW'] = '%s kan visa detta forum';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_READ'] = '%s kan läsa inlägg i det här forumet';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_POST'] = '%s kan posta inlägg i det här forumet';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_REPLY'] = '%s kan svara på inlägg i detta forum';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_EDIT'] = '%s kan redigera inlägg i det här forumet';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_DELETE'] = '%s kan ta bort inlägg i det här forumet';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_STICKY'] = '%s kan skriva klistrade ämnen / meddelanden i detta forum';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_ANNOUNCE'] = '%s kan posta meddelanden i detta forum';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_VOTE'] = '%s kan rösta i omröstningar i detta forum';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_POLLCREATE'] = '%s kan skapa omröstningar i detta forum';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_ATTACHMENTS'] = '%s kan posta bifogade filer';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_DOWNLOAD'] = '%s kan ladda ner bilagor';

// Misc
$lang['SF_SHOW_ON_INDEX'] = 'Visa på huvudsidan';
$lang['SF_PARENT_FORUM'] = 'Huvudforum';
$lang['SF_NO_PARENT'] = 'Ingen förälder forum';
$lang['TEMPLATE'] = 'Mall';
$lang['SYNC'] = 'Sync';

// Mods
$lang['MAX_NEWS_TITLE'] = 'Max. längd nyheter';
$lang['NEWS_COUNT'] = 'Hur många nyheter';
$lang['NEWS_FORUM_ID'] = 'Från vad forum för att visa <br /> <h6>Of flera forum höjer, separerade med kommatecken. Exempel 1,2,3</h6>';
$lang['NOAVATAR'] = 'Ingen avatar';
$lang['TRACKER_STATS'] = 'Statistik om tracker';
$lang['WHOIS_INFO'] = 'Information om IP-adress';
$lang['SHOW_MOD_HOME_PAGE'] = 'Visa på den moderatorer index.php';
$lang['SHOW_BOARD_STARTED_INDEX'] = 'Show board start date on index.php';
$lang['PREMOD_HELP'] = '<h4><span class="tor-icon tor-dup">&#8719;</span> Pre-moderation</h4> <h6>If du inte har utdelning till status av v, #, eller T i detta avsnitt, inklusive avsnitt, fördelningen kommer automatiskt att få detta status</h6>';
$lang['TOR_COMMENT'] = '<h4>Kommentera status för en distribution</h4> <h6>Kommentaren låter dig peka ut de fel som gjorts för utgivaren. Om statusarna är ofullständiga finns ett formulär för releasesvar tillgängligt för releasehanteraren för att korrigera releasen</h6>';
$lang['SEED_BONUS_ADD'] = '<h4>Adding utsäde bonus </h4> <h6> Antal olika distributioner som delas ut av användaren och storleken på bonusar för dem (laddning gånger i timmen) </h6>';
$lang['SEED_BONUS_RELEASE'] = 'till N-antal utgåvor';
$lang['SEED_BONUS_POINTS'] = 'bonusar i en timme';
$lang['SEED_BONUS_TOR_SIZE'] = '<h4>Minimum distribution som kommer att delas ut bonusar </h4> <h6> Om du vill beräkna bonusar för alla distribution, lämna det tomt. </h6>';
$lang['SEED_BONUS_USER_REGDATA'] = '<h4>Minimum längd av användaren tracker, efter som kommer att delas ut bonusar </h4> <h6> Om du vill samla bonusar till alla användare, lämna det tomt. </h6>';
$lang['SEED_BONUS_WARNING'] = 'UPPMÄRKSAMHET! Frö Bonusar bör vara i stigande ordning';
$lang['SEED_BONUS_EXCHANGE'] = 'Konfigurera Exchange Sid Bonusar';
$lang['SEED_BONUS_ROPORTION'] = 'Andelen tillägg för utbyte av bonusar på GB';

// Modules, this replaces the keys used
$lang['CONTROL_PANEL'] = 'Kontrollpanelen';
$lang['SHADOW_ATTACHMENTS'] = 'Skugga Bifogade Filer';
$lang['FORBIDDEN_EXTENSIONS'] = 'Förbjudet Tillägg';
$lang['EXTENSION_CONTROL'] = 'Förlängning Kontroll';
$lang['EXTENSION_GROUP_MANAGE'] = 'Förlängning Grupper Kontroll';
$lang['SPECIAL_CATEGORIES'] = 'Särskilda Kategorier';
$lang['SYNC_ATTACHMENTS'] = 'Synkronisera Bilagor';
$lang['QUOTA_LIMITS'] = 'Kvotgränser';

// Attachments -> Management
$lang['ATTACH_SETTINGS'] = 'Bilaga Inställningar';
$lang['MANAGE_ATTACHMENTS_EXPLAIN'] = 'Här kan du konfigurera de Viktigaste Inställningarna för Fastsättning Mod. Om du trycker på Test-Knappen Inställningar, Kvarstad Mod gör några System Tester för att vara säker på att Mod kommer att fungera ordentligt. Om du har problem med att ladda upp Filer kan du köra det här Testet för att få en detaljerad fel-meddelande.';
$lang['ATTACH_FILESIZE_SETTINGS'] = 'Bilaga Filstorlek Inställningar';
$lang['ATTACH_NUMBER_SETTINGS'] = 'Bilaga Antalet Inställningar';
$lang['ATTACH_OPTIONS_SETTINGS'] = 'Bilaga Alternativ';

$lang['UPLOAD_DIRECTORY'] = 'Ladda Upp Katalogen';
$lang['UPLOAD_DIRECTORY_EXPLAIN'] = 'Ange en relativ sökväg från din TorrentPier installation till Bilagor upload katalogen. Ange till exempel "filer" om din TorrentPier Installation ligger på https://www.yourdomain.com/torrentpier och den Bifogade filen Upload Katalogen ligger på https://www.yourdomain.com/torrentpier/files.';
$lang['ATTACH_IMG_PATH'] = 'Posta Bifogade Ikonen';
$lang['ATTACH_IMG_PATH_EXPLAIN'] = 'Denna Bild visas bredvid Bifogade Länkar i enskilda Inlägg. Lämna det här fältet tomt om du inte vill att en ikon ska visas. Denna Inställning kommer att skrivas över av de Inställningar som i Förlängningen Grupper Förvaltning.';
$lang['ATTACH_TOPIC_ICON'] = 'Bilaga Ämne Ikonen';
$lang['ATTACH_TOPIC_ICON_EXPLAIN'] = 'Denna Bild visas innan ämnen med Bilagor. Lämna det här fältet tomt om du inte vill att en ikon ska visas.';
$lang['ATTACH_DISPLAY_ORDER'] = 'Bifogad Fil Visas';
$lang['ATTACH_DISPLAY_ORDER_EXPLAIN'] = 'Här kan du välja om du vill visa Bilagor i Inlägg/PMs-i Fallande Filetime Ordning (Nyaste Bifogad fil Först) eller Stigande Filetime För (Äldsta Bifogad fil Först).';
$lang['SHOW_APCP'] = 'Använd den nya kontrollpanelen program';
$lang['SHOW_APCP_EXPLAIN'] = 'Välj om du vill använda en separat kontrollpanel program (ja), eller den gamla metoden med två lådor för program och applikationer (ingen) i meddelanderutan. Det är svårt att förklara hur det ser ut, så försök att för dig själv.';

$lang['MAX_FILESIZE_ATTACH'] = 'Filstorlek';
$lang['MAX_FILESIZE_ATTACH_EXPLAIN'] = 'Maximal filstorlek för Bifogade filer. Ett värde på 0 innebär "obegränsad". Denna Inställning är begränsad av din Server-Konfiguration. Till exempel, om din php-Konfiguration tillåter bara max 2 MB uppladdning, detta kan inte skrivas över av Mod.';
$lang['ATTACH_QUOTA'] = 'Bilaga Kvot';
$lang['ATTACH_QUOTA_EXPLAIN'] = 'Maximala Utrymme som ALLA Bilagor kan hålla på ditt Webbutrymme. Ett värde på 0 innebär "obegränsad".';
$lang['MAX_FILESIZE_PM'] = 'Maximal Filstorlek i Privata Meddelanden-Mappen';
$lang['MAX_FILESIZE_PM_EXPLAIN'] = 'Maximala Utrymme som Bilagor kan använda upp i varje Användares Privata Meddelande-rutan. Ett värde på 0 innebär "obegränsad".';
$lang['DEFAULT_QUOTA_LIMIT'] = 'Standard Kvot';
$lang['DEFAULT_QUOTA_LIMIT_EXPLAIN'] = 'Här har du möjlighet att välja Standard Kvot som tilldelats automatiskt till nyligen registrerade Användare och Användare utan en definierad storleksgräns. Alternativet "Ingen Kvot" för att inte använda någon Bilaga Kvoter, istället använder den förvalda Inställningar som du har definierat inom denna Förvaltning Panel.';

$lang['MAX_ATTACHMENTS'] = 'Maximalt Antal Bilagor';
$lang['MAX_ATTACHMENTS_EXPLAIN'] = 'Det maximala antalet bilagor accepteras i ett inlägg.';
$lang['MAX_ATTACHMENTS_PM'] = 'Maximalt antal Bilagor i ett Privat Meddelande';
$lang['MAX_ATTACHMENTS_PM_EXPLAIN'] = 'Ange det högsta antalet bilagor till den användare som är tillåtna att ingå i ett privat meddelande.';

$lang['DISABLE_MOD'] = 'Inaktivera Bilaga Mod';
$lang['DISABLE_MOD_EXPLAIN'] = 'Detta alternativ är främst för att testa nya mallar eller teman, inaktiveras alla bilagor Funktioner, utom Admin Panel.';
$lang['PM_ATTACHMENTS'] = 'Att Bifogade filer i Privata Meddelanden';
$lang['PM_ATTACHMENTS_EXPLAIN'] = 'Tillåt/tillåt inte bifoga filer i Privata Meddelanden.';
$lang['ATTACHMENT_TOPIC_REVIEW'] = 'Visa program i den översyn av kommunikation ämnen när du skriver ett svar?';
$lang['ATTACHMENT_TOPIC_REVIEW_EXPLAIN'] = 'Om du sätter i ett "ja", alla program kommer att visas i den översyn av kommunikation ämnen.';

// Attachments -> Shadow Attachments
$lang['SHADOW_ATTACHMENTS_EXPLAIN'] = 'Här kan du ta bort bifogad fil data från inläggen när de filer som saknas från ditt filsystem och ta bort filer som inte längre är anslutna till någon av inläggen. Du kan ladda ner eller visa en fil om du klickar på den, om ingen länk finns, filen inte finns.';
$lang['SHADOW_ATTACHMENTS_FILE_EXPLAIN'] = 'Ta bort alla bifogade filer som finns på ditt filsystem och inte tilldelats en befintlig post.';
$lang['SHADOW_ATTACHMENTS_ROW_EXPLAIN'] = 'Ta bort alla meddelanden bilaga uppgifter för filer som inte finns på ditt filsystem.';
$lang['EMPTY_FILE_ENTRY'] = 'Tom Fil Inlägg';

// Attachments -> Sync
$lang['SYNC_THUMBNAIL_RESETTED'] = 'Miniatyr resetted för Fastsättning: %s'; // replace %s with physical Filename
$lang['ATTACH_SYNC_FINISHED'] = 'Synkronisering av bilaga slutförd.';
$lang['SYNC_TOPICS'] = 'Sync Ämnen';
$lang['SYNC_POSTS'] = 'Sync Inlägg';
$lang['SYNC_THUMBNAILS'] = 'Sync Miniatyrer';

// Extensions -> Extension Control
$lang['MANAGE_EXTENSIONS'] = 'Hantera Tillägg';
$lang['MANAGE_EXTENSIONS_EXPLAIN'] = 'Här kan du hantera dina Filändelser. Om du vill tillåta/inte tillåta en Förlängning för att laddas upp, vänligen använd Förlängning Grupper Förvaltning.';
$lang['EXPLANATION'] = 'Förklaring';
$lang['EXTENSION_GROUP'] = 'Förlängning Grupp';
$lang['INVALID_EXTENSION'] = 'Ogiltiga Förlängning';
$lang['EXTENSION_EXIST'] = 'Förlängning %s redan finns'; // replace %s with the Extension
$lang['UNABLE_ADD_FORBIDDEN_EXTENSION'] = 'Förlängning %s är förbjudet, du kan inte lägga till det tillåtna Tillägg'; // replace %s with Extension

// Extensions -> Extension Groups Management
$lang['MANAGE_EXTENSION_GROUPS'] = 'Hantera Grupper Förlängning';
$lang['MANAGE_EXTENSION_GROUPS_EXPLAIN'] = 'Här kan du lägga till, ta bort och ändra din Förlängning Grupper, kan du inaktivera Tillägget Grupper, tilldela en särskild Kategori för dem, ändra ladda ner mekanism och du kan definiera Ladda upp en Ikon som kommer att visas framför en Bilaga som hör till Gruppen.';
$lang['SPECIAL_CATEGORY'] = 'Särskild Kategori';
$lang['CATEGORY_IMAGES'] = 'Bilder';
$lang['ALLOWED'] = 'Tillåtna';
$lang['ALLOWED_FORUMS'] = 'Tillåtna Forum';
$lang['EXT_GROUP_PERMISSIONS'] = 'Grupp Behörigheter';
$lang['DOWNLOAD_MODE'] = 'Ladda Ner-Läge';
$lang['UPLOAD_ICON'] = 'Ladda Upp Ikonen';
$lang['MAX_GROUPS_FILESIZE'] = 'Maximal Filstorlek';
$lang['EXTENSION_GROUP_EXIST'] = 'Förlängning Grupp %s redan finns'; // replace %s with the group name

// Extensions -> Special Categories
$lang['MANAGE_CATEGORIES'] = 'Hantera Speciella Kategorier';
$lang['MANAGE_CATEGORIES_EXPLAIN'] = 'Here you can configure the Special Categories. You can set up Special Parameters and Conditions for the Special Categories assigned to an Extension Group.';
$lang['SETTINGS_CAT_IMAGES'] = 'Inställningar för Speciella Kategori: Bilder';
$lang['SETTINGS_CAT_FLASH'] = 'Inställningar för Speciella Kategori: Flash-Filer';
$lang['DISPLAY_INLINED'] = 'Visa Bilder Inlined';
$lang['DISPLAY_INLINED_EXPLAIN'] = 'Välj om du vill visa bilder direkt i inlägg (ja), eller att visa bilder som en länk ?';
$lang['MAX_IMAGE_SIZE'] = 'Maximal Bild Dimensioner';
$lang['MAX_IMAGE_SIZE_EXPLAIN'] = 'Här kan du definiera en gräns för högsta tillåtna dimensioner för att fästas (Bredd x Höjd i pixlar).<br />If det är som att 0x0, den här funktionen är inaktiverad. Med lite Bilder här Funktionen kommer inte att fungera på grund av begränsningar i PHP.';
$lang['IMAGE_LINK_SIZE'] = 'Länk Till Bild Dimensioner';
$lang['IMAGE_LINK_SIZE_EXPLAIN'] = 'Om denna Dimension som definieras av en Bild är nått, kommer Bilden att visas som en Länk, i stället visar det inlined,<br />if Inline Vy är aktiverad (Bredd x Höjd i pixlar).<br />If det är som att 0x0, den här funktionen är inaktiverad. Med lite Bilder här Funktionen kommer inte att fungera på grund av begränsningar i PHP.';
$lang['ASSIGNED_GROUP'] = 'Tilldelade Gruppen';

$lang['IMAGE_CREATE_THUMBNAIL'] = 'Skapa Miniatyrbilder';
$lang['IMAGE_MIN_THUMB_FILESIZE'] = 'Minsta Filstorlek Miniatyr';
$lang['IMAGE_MIN_THUMB_FILESIZE_EXPLAIN'] = 'Om en Bild som är mindre än detta definieras Filstorlek, inga Miniatyrbilder att skapas, eftersom den är liten nog.';

// Extensions -> Forbidden Extensions
$lang['MANAGE_FORBIDDEN_EXTENSIONS'] = 'Hantera Förbjudet Tillägg';
$lang['MANAGE_FORBIDDEN_EXTENSIONS_EXPLAIN'] = 'Här kan du lägga till eller ta bort den förbjudna tillägg. Tillägg php, php3 och php4 är förbjudet enligt standard av säkerhetsskäl kan du inte ta bort dem.';
$lang['FORBIDDEN_EXTENSION_EXIST'] = 'Den förbjudna Förlängning %s redan finns'; // replace %s with the extension
$lang['EXTENSION_EXIST_FORBIDDEN'] = 'Förlängning %s är definierade i din tillåtna Tillägg, ta bort det deras innan du lägger till den här.'; // replace %s with the extension

// Extensions -> Extension Groups Control -> Group Permissions
$lang['GROUP_PERMISSIONS_TITLE_ADMIN'] = 'Förlängning Grupp Behörigheter -> \'%s\''; // Replace %s with the Groups Name
$lang['GROUP_PERMISSIONS_EXPLAIN'] = 'Här har du möjlighet att begränsa den valda Extension Grupp till Forum av ditt val (som definieras i den Tillåtna Forum Box). Standard är att tillåta Förlängning Grupper för att alla Forum som Användaren har möjlighet att Bifoga Filer till (normalt sätt Bilagan Mod gjorde det sedan början). Lägg bara till de Forum du vill ha en Förlängning Grupp (Tillägg inom denna Grupp) för att vara tillåtna att det finns, standard är ALLA FORUM kommer att försvinna när du lägger till Forum för att Listan. Du kan lägga till ALLA FORUM vid varje given Tidpunkt. Om du lägger till ett Forum för att din Styrelse och Behörigheten är inställd på ALLA FORUM ingenting kommer att förändras. Men om du har ändrat och begränsad tillgång till vissa Forum, kan du titta in här för att lägga till ditt nyskapade Forumet. Det är lätt att göra detta automatiskt, men detta kommer att tvinga dig att redigera en massa Filer, därför har jag valt den vägen är det nu. Tänk på att alla dina Forum kommer att listas här.';
$lang['NOTE_ADMIN_EMPTY_GROUP_PERMISSIONS'] = 'OBS:<br />Within nedanstående Forum din Användare är normalt tillåtet att bifoga filer, men eftersom ingen Förlängning Grupp är tillåtet att fästas där dina Användare kan inte bifoga något. Om de försöker, de kommer att ta emot Meddelanden. Du kanske vill ställa in Tillstånd "Skicka Filer" för att ADMIN på dessa Forum.<br /><br />';
$lang['ADD_FORUMS'] = 'Lägg Till Forum';
$lang['ADD_SELECTED'] = 'Lägg Till Markerade';
$lang['PERM_ALL_FORUMS'] = 'ALLA FORUM';

// Attachments -> Quota Limits
$lang['MANAGE_QUOTAS'] = 'Hantera Bifogade Filer Kvotgränser';
$lang['MANAGE_QUOTAS_EXPLAIN'] = 'Här har du möjlighet att lägga till/ta bort/ändra kvotgränser. Du kan tilldela dessa Kvoter för Användare och Grupper senare. För att tilldela en Kvot till en Användare, måste du gå till Användare->Förvaltning, väljer du Användaren och du kommer att se Alternativ i botten. För att tilldela en Kvot till en Grupp, gå till Grupper->Förvaltning, välj den Grupp du vill redigera det, och du kommer att se konfigurationsinställningar. Om du vill se vilka Användare och Grupper som har tilldelats till en viss Kvot, klicka på "Visa" till vänster om Kvoten Beskrivning.';
$lang['ASSIGNED_USERS'] = 'Tilldelade Användare';
$lang['ASSIGNED_GROUPS'] = 'Tilldelade Grupper';
$lang['QUOTA_LIMIT_EXIST'] = 'Den Kvot %s redan finns.'; // Replace %s with the Quota Description

// Attachments -> Control Panel
$lang['CONTROL_PANEL_TITLE'] = 'Bifogad Fil Kontrollpanelen';
$lang['CONTROL_PANEL_EXPLAIN'] = 'Här kan du se och hantera alla bifogade filer baserat på Användare, Bilagor, Åsikter osv...';
$lang['FILECOMMENT'] = 'Fil Kommentar';

// Control Panel -> Search
$lang['SEARCH_WILDCARD_EXPLAIN'] = 'Använd * som jokertecken för partiella matchningar';
$lang['SIZE_SMALLER_THAN'] = 'Bifogad fil storlek mindre än (byte)';
$lang['SIZE_GREATER_THAN'] = 'Bifogad fil storlek större än (byte)';
$lang['COUNT_SMALLER_THAN'] = 'Ladda ner antalet är mindre än';
$lang['COUNT_GREATER_THAN'] = 'Ladda ner antalet är större än';
$lang['MORE_DAYS_OLD'] = 'Mer än så här många dagar gammal';
$lang['NO_ATTACH_SEARCH_MATCH'] = 'Inga Bilagor träffade din sökkriterier';

// Control Panel -> Statistics
$lang['NUMBER_OF_ATTACHMENTS'] = 'Antal Bifogade filer';
$lang['TOTAL_FILESIZE'] = 'Total Filstorlek';
$lang['NUMBER_POSTS_ATTACH'] = 'Antal Inlägg med Bilagor';
$lang['NUMBER_TOPICS_ATTACH'] = 'Antal Ämnen med Bilagor';
$lang['NUMBER_USERS_ATTACH'] = 'Oberoende Användare Postat Bifogade Filer';
$lang['NUMBER_PMS_ATTACH'] = 'Totalt Antal Bifogade filer i Privata Meddelanden';
$lang['ATTACHMENTS_PER_DAY'] = 'Bilagor per dag';

// Control Panel -> Attachments
$lang['STATISTICS_FOR_USER'] = 'Bilaga Statistik för %s'; // replace %s with username
$lang['DOWNLOADS'] = 'Nedladdningar';
$lang['POST_TIME'] = 'Inlägg';
$lang['POSTED_IN_TOPIC'] = 'Publicerat i Ämnet';
$lang['SUBMIT_CHANGES'] = 'Skicka In Förändringar';

// Sort Types
$lang['SORT_ATTACHMENTS'] = 'Bifogade filer';
$lang['SORT_SIZE'] = 'Storlek';
$lang['SORT_FILENAME'] = 'Filnamn';
$lang['SORT_COMMENT'] = 'Kommentar';
$lang['SORT_EXTENSION'] = 'Förlängning';
$lang['SORT_DOWNLOADS'] = 'Nedladdningar';
$lang['SORT_POSTTIME'] = 'Inlägg';

// View Types
$lang['VIEW_STATISTIC'] = 'Statistik';
$lang['VIEW_SEARCH'] = 'Sök';
$lang['VIEW_USERNAME'] = 'Användarnamn';
$lang['VIEW_ATTACHMENTS'] = 'Bifogade filer';

// Successfully updated
$lang['ATTACH_CONFIG_UPDATED'] = 'Bilaga Konfiguration uppdaterats';
$lang['CLICK_RETURN_ATTACH_CONFIG'] = 'Klicka %sHere%s för att återgå till Bilaga Konfiguration';
$lang['TEST_SETTINGS_SUCCESSFUL'] = 'Inställningar för Testet har varit klar, konfiguration verkar vara bra.';

// Some basic definitions
$lang['ATTACHMENTS'] = 'Bifogade filer';
$lang['EXTENSIONS'] = 'Tillägg';
$lang['EXTENSION'] = 'Förlängning';

$lang['RETURN_CONFIG'] = '%sReturn att Configuration%s';
$lang['CONFIG_UPD'] = 'Konfiguration Uppdaterats';
$lang['SET_DEFAULTS'] = 'Återställ standardvärden';

// Forum config
$lang['FORUM_CFG_EXPL'] = 'Forum config';

$lang['BT_SELECT_FORUMS'] = 'Forum alternativ:';
$lang['BT_SELECT_FORUMS_EXPL'] = 'håll ned <i>Ctrl</i> när du väljer flera forum';

$lang['REG_TORRENTS'] = 'Registrera dig för torrents';
$lang['DISALLOWED'] = 'Förbjudet';
$lang['ALLOW_REG_TRACKER'] = 'Tillåtna forum för att registrera dig .torrents på tracker';
$lang['ALLOW_PORNO_TOPIC'] = 'Tillåtet att publicera innehåll som är 18+';
$lang['SHOW_DL_BUTTONS'] = 'Visa knappar för att manuellt ändra DL-status';
$lang['SELF_MODERATED'] = 'Användare kan <b>move</b> sina frågor till ett annat forum';

$lang['BT_ANNOUNCE_URL_HEAD'] = 'Meddela URL';
$lang['BT_ANNOUNCE_URL'] = 'Meddela url';
$lang['BT_ANNOUNCE_URL_EXPL'] = 'du kan definiera ytterligare tillåtna webbadresser i "includes/torrent_announce_urls.php"';
$lang['BT_DISABLE_DHT'] = 'Inaktivera DHT nätverk';
$lang['BT_DISABLE_DHT_EXPL'] = 'Inaktivera Peer Exchange och DHT (rekommenderas för privata nät, endast url, meddela)';
$lang['BT_PRIVATE_TRACKER'] = 'This tracker is private: file listing (for guests), DHT | PEX are disabled';
$lang['BT_PRIVATE_TORRENT'] = 'The creator of this torrent made it private';
$lang['BT_CHECK_ANNOUNCE_URL'] = 'Kontrollera announce url';
$lang['BT_CHECK_ANNOUNCE_URL_EXPL'] = 'registrera dig på tracker endast tillåtet webbadresser';
$lang['BT_REPLACE_ANN_URL'] = 'Byt announce url';
$lang['BT_REPLACE_ANN_URL_EXPL'] = 'ersätter original announce url med din standard .torrent filer';
$lang['BT_DEL_ADDIT_ANN_URLS'] = 'Ta bort alla extra meddela webbadresser';
$lang['BT_DEL_ADDIT_ANN_URLS_EXPL'] = 'om torrent innehåller adresser till andra trackers, kommer de att tas bort';

$lang['BT_SHOW_PEERS_HEAD'] = 'Kamrater-Lista';
$lang['BT_SHOW_PEERS'] = 'Visa jämnåriga (seeders och leechers)';
$lang['BT_SHOW_PEERS_EXPL'] = 'detta kommer att visa seeders/leechers listan ovan ämnet med torrent';
$lang['BT_SHOW_PEERS_MODE'] = 'Som standard visas kamrater som:';
$lang['BT_SHOW_PEERS_MODE_COUNT'] = 'Räkna bara';
$lang['BT_SHOW_PEERS_MODE_NAMES'] = 'Namn bara';
$lang['BT_SHOW_PEERS_MODE_FULL'] = 'Fullständig information';
$lang['BT_ALLOW_SPMODE_CHANGE'] = 'Tillåt "Fullständiga uppgifter" - läge';
$lang['BT_ALLOW_SPMODE_CHANGE_EXPL'] = 'om "nej", bara standard peer-visningsläget kommer att vara tillgänglig';
$lang['BT_SHOW_IP_ONLY_MODER'] = 'Kamrater\' <b>IP</b>s är synliga för endast moderatorer';
$lang['BT_SHOW_PORT_ONLY_MODER'] = 'Kamrater\' <b>Port</b>s är synliga för endast moderatorer';

$lang['BT_SHOW_DL_LIST_HEAD'] = 'DL-Lista';
$lang['BT_SHOW_DL_LIST'] = 'Visa DL-Lista i Ladda ner ämnen';
$lang['BT_DL_LIST_ONLY_1ST_PAGE'] = 'Visa DL-endast på första sidan i ämnen';
$lang['BT_DL_LIST_ONLY_COUNT'] = 'Visa bara antalet användare';
$lang['BT_SHOW_DL_LIST_BUTTONS'] = 'Visa knappar för att manuellt ändra DL-status';
$lang['BT_SHOW_DL_BUT_WILL'] = $lang['DLWILL'];
$lang['BT_SHOW_DL_BUT_DOWN'] = $lang['DLDOWN'];
$lang['BT_SHOW_DL_BUT_COMPL'] = $lang['DLCOMPLETE'];
$lang['BT_SHOW_DL_BUT_CANCEL'] = $lang['DLCANCEL'];

$lang['BT_ADD_AUTH_KEY_HEAD'] = 'Passkey';
$lang['BT_ADD_AUTH_KEY'] = 'Möjligt att lägga till nyckeln till torrent-filer innan du laddar ner';

$lang['BT_TOR_BROWSE_ONLY_REG_HEAD'] = 'Torrent webbläsare (tracker)';
$lang['BT_TOR_BROWSE_ONLY_REG'] = 'Torrent webbläsare (tracker.php) endast tillgänglig för inloggade användare';
$lang['BT_SEARCH_BOOL_MODE'] = 'Låt boolean fulltext-sökningar';
$lang['BT_SEARCH_BOOL_MODE_EXPL'] = 'använd *, +, -,.. i sökningar';

$lang['BT_SHOW_DL_STAT_ON_INDEX_HEAD'] = "Övrigt";
$lang['BT_SHOW_DL_STAT_ON_INDEX'] = "Visa användare UL/DL statistik på toppen av forumets huvudsida";
$lang['BT_NEWTOPIC_AUTO_REG'] = 'Automatiskt registrera torrent på tracker för nya ämnen';
$lang['BT_SET_DLTYPE_ON_TOR_REG'] = 'Ändra ämne status till "Ladda ner" samtidigt som man registrerar torrent på tracker';
$lang['BT_SET_DLTYPE_ON_TOR_REG_EXPL'] = 'kommer att byta ämne typ att "Ladda ner" - oavsett forum inställningar';
$lang['BT_UNSET_DLTYPE_ON_TOR_UNREG'] = 'Ändra ämne status till "Normala" medan avregistrera torrent från tracker';

// Release
$lang['TEMPLATE_DISABLE'] = 'Mall för funktionshindrade';
$lang['FOR_NEW_TEMPLATE'] = 'för nya mönster!';
$lang['CHANGED'] = 'Ändrat';
$lang['REMOVED'] = 'Bort';
$lang['QUESTION'] = 'Confirm are you sure you want to perform this action';

$lang['CRON_LIST'] = 'Cron lista';
$lang['CRON_ID'] = 'ID';
$lang['CRON_ACTIVE'] = 'På';
$lang['CRON_ACTIVE_EXPL'] = 'Aktiva uppgifter';
$lang['CRON_TITLE'] = 'Titel';
$lang['CRON_SCRIPT'] = 'Manus';
$lang['CRON_SCHEDULE'] = 'Schema';
$lang['CRON_LAST_RUN'] = 'Sista Körningen';
$lang['CRON_NEXT_RUN'] = 'Nästa Körning';
$lang['CRON_RUN_COUNT'] = 'Går';
$lang['CRON_MANAGE'] = 'Hantera';
$lang['CRON_OPTIONS'] = 'Cron alternativ';
$lang['CRON_DISABLED_WARNING'] = 'Varning! Att köra cron-skript är inaktiverat. För att aktivera det, ställ in variabeln APP_CRON_ENABLED.';

$lang['CRON_ENABLED'] = 'Cron aktiverad';
$lang['CRON_CHECK_INTERVAL'] = 'Cron in intervall (sek)';

$lang['WITH_SELECTED'] = 'Med utvalda';
$lang['NOTHING'] = 'göra ingenting';
$lang['CRON_RUN'] = 'Kör';
$lang['CRON_DEL'] = 'Ta bort';
$lang['CRON_DISABLE'] = 'Inaktivera';
$lang['CRON_ENABLE'] = 'Aktivera';

$lang['RUN_MAIN_CRON'] = 'Börja cron';
$lang['ADD_JOB'] = 'Lägg till cron-jobb';
$lang['DELETE_JOB'] = 'Är du säker på att du vill radera cron-jobb?';
$lang['CRON_WORKS'] = 'Cron är nu fungerar eller är trasig -> ';
$lang['REPAIR_CRON'] = 'Reparation Cron';

$lang['CRON_EDIT_HEAD_EDIT'] = 'Redigera jobb';
$lang['CRON_EDIT_HEAD_ADD'] = 'Lägg till jobb';
$lang['CRON_SCRIPT_EXPL'] = 'namnet på skriptet från "includes/cron/jobb/"';
$lang['SCHEDULE'] = [
    'select' => '&raquo; Välj start',
    'hourly' => 'tim',
    'daily' => 'dagligen',
    'weekly' => 'vecka',
    'monthly' => 'månad',
    'interval' => 'intervall'
];
$lang['NOSELECT'] = 'Välj';
$lang['RUN_DAY'] = 'Kör dag';
$lang['RUN_DAY_EXPL'] = 'den dag då detta jobb springa';
$lang['RUN_TIME'] = 'Körning';
$lang['RUN_TIME_EXPL'] = 'tiden när detta arbete köra (exempelvis 05:00:00)';
$lang['RUN_ORDER'] = 'Kör så';
$lang['LAST_RUN'] = 'Sista Körningen';
$lang['NEXT_RUN'] = 'Nästa Körning';
$lang['RUN_INTERVAL'] = 'Köra intervall';
$lang['RUN_INTERVAL_EXPL'] = 'exempelvis 00:10:00';
$lang['LOG_ENABLED'] = 'Logga aktiverad';
$lang['LOG_FILE'] = 'Loggfilen';
$lang['LOG_FILE_EXPL'] = 'filen för att spara loggen';
$lang['LOG_SQL_QUERIES'] = 'Logga in SQL-frågor';
$lang['FORUM_DISABLE'] = 'Inaktivera styrelsen';
$lang['BOARD_DISABLE_EXPL'] = 'inaktivera styrelsen när detta jobb är att köra';
$lang['RUN_COUNTER'] = 'Strider';

$lang['JOB_REMOVED'] = 'Problemet har tagits bort';
$lang['SCRIPT_DUPLICATE'] = 'Manus <b>' . @$_POST['cron_script'] . '</b> redan!';
$lang['TITLE_DUPLICATE'] = 'Uppgift Namn <b>' . @$_POST['cron_title'] . '</b> redan!';
$lang['CLICK_RETURN_JOBS_ADDED'] = '%sReturn tillägg problem%s';
$lang['CLICK_RETURN_JOBS'] = '%sBack till Uppgift Scheduler%s';

$lang['REBUILD_SEARCH'] = 'Återskapa Sökindex';
$lang['REBUILD_SEARCH_DESC'] = 'Denna mod kommer att indexera varje inlägg i ert forum, ombyggnad sök bord. Du kan sluta när du vill och nästa gång du kör det igen kommer du att ha möjlighet att fortsätta från där du slutade.<br /><br />It kan ta lång tid att visa sina framsteg (beroende på "Inlägg per cykel" och "Tid"), så du behöver inte flytta från sina framsteg sida tills den är klar, såvida inte naturligtvis du vill avbryta den.';

// Input screen
$lang['STARTING_POST_ID'] = 'Börjar post_id';
$lang['STARTING_POST_ID_EXPLAIN'] = 'Första inlägget då behandlingen börjar from<br />You kan välja att starta från början eller från det inlägg du senast stoppas';

$lang['START_OPTION_BEGINNING'] = 'börja från början';
$lang['START_OPTION_CONTINUE'] = 'fortsättning från förra stoppas';

$lang['CLEAR_SEARCH_TABLES'] = 'Tydliga tabeller sök';
$lang['CLEAR_SEARCH_TABLES_EXPLAIN'] = '';
$lang['CLEAR_SEARCH_NO'] = 'INGA';
$lang['CLEAR_SEARCH_DELETE'] = 'Ta BORT';
$lang['CLEAR_SEARCH_TRUNCATE'] = 'TRUNKERA';

$lang['NUM_OF_POSTS'] = 'Antal inlägg';
$lang['NUM_OF_POSTS_EXPLAIN'] = 'Totalt antal inlägg att process<br />It är automatiskt ifyllt med det totala antalet/återstående tjänsterna finns i db';

$lang['POSTS_PER_CYCLE'] = 'Inlägg per cykel';
$lang['POSTS_PER_CYCLE_EXPLAIN'] = 'Antal inlägg att processen per cycle<br />Keep det låg för att undvika php/webserver tidsgränser';

$lang['REFRESH_RATE'] = 'Uppdateringsfrekvensen';
$lang['REFRESH_RATE_EXPLAIN'] = 'Hur mycket tid (sekunder) för att stanna inaktiv innan du flyttar till nästa behandling cycle<br />Usually behöver du inte ändra denna';

$lang['TIME_LIMIT'] = 'Tid';
$lang['TIME_LIMIT_EXPLAIN'] = 'Hur mycket tid (sekunder) efter behandling kan pågå innan de går vidare till nästa cykel';
$lang['TIME_LIMIT_EXPLAIN_SAFE'] = '<i>Your php (felsäkert läge) har en timeout av %s sek konfigurerad, så håll under denna value</i>';
$lang['TIME_LIMIT_EXPLAIN_WEBSERVER'] = '<i>Your webbserver har en timeout av %s sek konfigurerad, så håll under denna value</i>';

$lang['DISABLE_BOARD'] = 'Inaktivera styrelsen';
$lang['DISABLE_BOARD_EXPLAIN'] = 'Huruvida eller inte att inaktivera ditt styrelsen under bearbetning';
$lang['DISABLE_BOARD_EXPLAIN_ENABLED'] = 'Det kommer att aktiveras automatiskt efter slutet av behandlingen';
$lang['DISABLE_BOARD_EXPLAIN_ALREADY'] = '<i>Your styrelsen är redan disabled</i>';

// Information strings
$lang['INFO_PROCESSING_STOPPED'] = 'Du senast avbröt behandlingen på post_id %s (%s bearbetade inlägg) på %s';
$lang['INFO_PROCESSING_ABORTED'] = 'Du senast avbröt behandlingen på post_id %s (%s bearbetade inlägg) på %s';
$lang['INFO_PROCESSING_ABORTED_SOON'] = 'Vänligen vänta några minuter innan du fortsätter...';
$lang['INFO_PROCESSING_FINISHED'] = 'Du har framgångsrikt avslutat behandling (%s bearbetade inlägg) på %s';
$lang['INFO_PROCESSING_FINISHED_NEW'] = 'Du har framgångsrikt avslutat behandlingen på post_id %s (%s bearbetade inlägg) på %s,<br />but det har varit %s nya inlägg(s) efter det datum';

// Progress screen
$lang['REBUILD_SEARCH_PROGRESS'] = 'Bygga Söka Framsteg';

$lang['PROCESSED_POST_IDS'] = 'Bearbetade inlägg id : %s - %s';
$lang['TIMER_EXPIRED'] = 'Timern löpt ut vid %s sekunder. ';
$lang['CLEARED_SEARCH_TABLES'] = 'Rensat sök bord. ';
$lang['DELETED_POSTS'] = '%s inlägg(s) har tagits bort av dina användare under bearbetning. ';
$lang['PROCESSING_NEXT_POSTS'] = 'Bearbetning nästa %s inlägg(s). Var vänlig vänta...';
$lang['ALL_SESSION_POSTS_PROCESSED'] = 'Behandlas alla inlägg i den aktuella sessionen.';
$lang['ALL_POSTS_PROCESSED'] = 'Alla inlägg behandlades framgångsrikt.';
$lang['ALL_TABLES_OPTIMIZED'] = 'Alla sök bord var optimerad framgångsrikt.';

$lang['PROCESSING_POST_DETAILS'] = 'Bearbetning inlägg';
$lang['PROCESSED_POSTS'] = 'Bearbetade Inlägg';
$lang['PERCENT'] = 'Procent';
$lang['CURRENT_SESSION'] = 'Aktuella Sessionen';
$lang['TOTAL'] = 'Totalt';

$lang['PROCESS_DETAILS'] = 'från <b>%s</b> att <b>%s</b> (av totalt <b>%s</b>)';
$lang['PERCENT_COMPLETED'] = '%s %% klar';

$lang['PROCESSING_TIME_DETAILS'] = 'Aktuella sessionen detaljer';
$lang['PROCESSING_TIME'] = 'Handläggningstiden';
$lang['TIME_LAST_POSTS'] = 'Sista %s inlägg(s)';
$lang['TIME_FROM_THE_BEGINNING'] = 'Från början';
$lang['TIME_AVERAGE'] = 'Snitt per cykel';
$lang['TIME_ESTIMATED'] = 'Beräknad tills slut';

$lang['DATABASE_SIZE_DETAILS'] = 'Databasens storlek detaljer';
$lang['SIZE_CURRENT'] = 'Nuvarande';
$lang['SIZE_ESTIMATED'] = 'Beräknad målgång';
$lang['SIZE_SEARCH_TABLES'] = 'Sök Bord storlek';
$lang['SIZE_DATABASE'] = 'Databasens storlek';

$lang['ACTIVE_PARAMETERS'] = 'Aktiva parametrar';
$lang['POSTS_LAST_CYCLE'] = 'Bearbetade inlägg(s) den sista cykeln';
$lang['BOARD_STATUS'] = 'Styrelsen status';

$lang['INFO_ESTIMATED_VALUES'] = '(*) Alla beräknade värden är beräknade approximately<br />based på den aktuella klar procent och får inte representera den faktiska slutliga värden.<br />As avslutade procent ökar de beräknade värdena kommer att komma närmare den faktiska och kära.';

$lang['CLICK_RETURN_REBUILD_SEARCH'] = 'Klicka %shere%s att återvända för att Återuppbygga Sök';
$lang['REBUILD_SEARCH_ABORTED'] = 'Bygga upp sökandet avbröts vid post_id %s.<br /><br />If du avbruten medan behandlingen var på, måste du vänta några minuter tills du får Bygga Söka igen, så den sista cykeln kan avsluta.';
$lang['WRONG_INPUT'] = 'Du har skrivit lite fel värden. Vänligen kontrollera din input och försök igen.';

// Buttons
$lang['PROCESSING'] = 'Bearbetning...';
$lang['FINISHED'] = 'Klar';

$lang['BOT_TOPIC_MOVED_FROM_TO'] = 'Topic has been moved from forum [b]%s[/b] to forum [b]%s[/b].[br][b]Reason to move:[/b] %s[br][br]%s';
$lang['BOT_MESS_SPLITS'] = 'Ämnet har delats upp. Nytt ämne - [b]%s[/b][br][br]%s';
$lang['BOT_TOPIC_SPLITS'] = 'Ämnet har delats upp från [b]%s[/b][br][br]%s';

$lang['CALLSEED'] = 'Call seeds';
$lang['CALLSEED_EXPLAIN'] = 'Ta meddelande med en begäran att återgå till distribution';
$lang['CALLSEED_SUBJECT'] = 'Ladda ner hjälp %s';
$lang['CALLSEED_TEXT'] = 'Hej![br]Your hjälp behövs i utgivningen [url=%s]%s[/url][br]if du bestämmer dig för att hjälpa, men redan bort torrent-filen, kan du ladda ner det [url=%s]this[/url][br][br]i hoppas för din hjälp!';
$lang['CALLSEED_MSG_OK'] = 'Meddelandet har skickats till alla som laddat ner den här versionen';
$lang['CALLSEED_MSG_SPAM'] = 'Begäran har redan varit en gång, att du har skickat (och Förmodligen inte du)<br /><br />The nästa tillfälle att skicka en begäran om att <b>%s</b>.';
$lang['CALLSEED_HAVE_SEED'] = 'Ämne inte behöver hjälp (<b>Seeders:</b> %d)';

$lang['LOG_ACTION']['LOG_TYPE'] = [
    'mod_topic_delete' => 'Ämne:<br /> <b>deleted</b>',
    'mod_topic_move' => 'Ämne:<br /> <b>moved</b>',
    'mod_topic_lock' => 'Ämne:<br /> <b>closed</b>',
    'mod_topic_unlock' => 'Ämne:<br /> <b>opened</b>',
    'mod_topic_split' => 'Ämne:<br /> <b>split</b>',
    'mod_topic_set_downloaded' => 'Topic:<br /> <b>set downloaded</b>',
    'mod_topic_unset_downloaded' => 'Topic:<br /> <b>unset downloaded</b>',
    'mod_topic_change_tor_status' => 'Topic:<br /> <b>changed torrent status</b>',
    'mod_topic_change_tor_type' => 'Topic:<br /> <b>changed torrent type</b>',
    'mod_topic_tor_unregister' => 'Topic:<br /> <b>torrent unregistered</b>',
    'mod_topic_tor_register' => 'Topic:<br /> <b>torrent registered</b>',
    'mod_topic_tor_delete' => 'Topic:<br /> <b>torrent deleted</b>',
    'mod_topic_renamed' => 'Topic:<br /> <b>renamed</b>',
    'mod_post_delete' => 'Inlägg:<br /> <b>deleted</b>',
    'mod_post_pin' => 'Post:<br /> <b>pinned</b>',
    'mod_post_unpin' => 'Post:<br /> <b>unpinned</b>',
    'adm_user_delete' => 'Användare:<br /> <b>deleted</b>',
    'adm_user_ban' => 'Användare:<br /> <b>ban</b>',
    'adm_user_unban' => 'Användare:<br /> <b>unban</b>',
];

$lang['ACTS_LOG_ALL_ACTIONS'] = 'Alla åtgärder';
$lang['ACTS_LOG_SEARCH_OPTIONS'] = 'Åtgärder Logga in: Sök alternativ';
$lang['ACTS_LOG_FORUM'] = 'Forum';
$lang['ACTS_LOG_ACTION'] = 'Åtgärd';
$lang['ACTS_LOG_USER'] = 'Användare';
$lang['ACTS_LOG_LOGS_FROM'] = 'Loggar från ';
$lang['ACTS_LOG_FIRST'] = 'börjar med';
$lang['ACTS_LOG_DAYS_BACK'] = 'dagar tillbaka';
$lang['ACTS_LOG_TOPIC_MATCH'] = 'Ämne titel match';
$lang['ACTS_LOG_SORT_BY'] = 'Sortera efter';
$lang['ACTS_LOG_LOGS_ACTION'] = 'Åtgärd';
$lang['ACTS_LOG_USERNAME'] = 'Användarnamn';
$lang['ACTS_LOG_TIME'] = 'Tid';
$lang['ACTS_LOG_INFO'] = 'Info';
$lang['ACTS_LOG_FILTER'] = 'Filter';
$lang['ACTS_LOG_TOPICS'] = 'Ämnen:';
$lang['ACTS_LOG_OR'] = 'eller';

$lang['RELEASE'] = 'Släpp Mallar';
$lang['RELEASES'] = 'Släpper';

$lang['BACK'] = 'Tillbaka';
$lang['ERROR_FORM'] = 'Ogiltigt fält';
$lang['RELEASE_WELCOME'] = 'Vänligen fyll i releaseformuläret';
$lang['NEW_RELEASE'] = 'Ny utgåva';
$lang['NEXT'] = 'Fortsätt';
$lang['OTHER'] = 'Andra';
$lang['OTHERS'] = 'Others';
$lang['ALL'] = 'All';

$lang['TPL_EMPTY_FIELD'] = 'Du måste fylla i fältet <b>%s</b>';
$lang['TPL_EMPTY_SEL'] = 'Du måste välja <b>%s</b>';
$lang['TPL_NOT_NUM'] = '<b>%s</b> - Inte en numerisk';
$lang['TPL_NOT_URL'] = '<b>%s</b> - Måste vara https:// URL';
$lang['TPL_NOT_IMG_URL'] = '<b>%s</b> - Måste vara https:// IMG_URL';
$lang['TPL_PUT_INTO_SUBJECT'] = 'lägg till ämnet';
$lang['TPL_POSTER'] = 'affisch';
$lang['TPL_REQ_FILLING'] = 'kräver fyllning';
$lang['TPL_NEW_LINE'] = 'ny linje';
$lang['TPL_NEW_LINE_AFTER'] = 'ny linje efter titeln';
$lang['TPL_NUM'] = 'antal';
$lang['TPL_URL'] = 'URL';
$lang['TPL_IMG'] = 'bild';
$lang['TPL_PRE'] = 'pre';
$lang['TPL_SPOILER'] = 'spoiler';
$lang['TPL_IN_LINE'] = 'i samma linje';
$lang['TPL_HEADER_ONLY'] = 'endast i en titel';

$lang['SEARCH_INVALID_USERNAME'] = 'Ogiltigt användarnamn har angetts för att söka';
$lang['SEARCH_INVALID_EMAIL'] = 'Ogiltig e-postadress har angetts för att söka';
$lang['SEARCH_INVALID_IP'] = 'Ogiltig IP-adress har angetts för att söka';
$lang['SEARCH_INVALID_GROUP'] = 'Ogiltig grupp har angetts för att söka';
$lang['SEARCH_INVALID_RANK'] = 'Ogiltig rankning har angetts för sökning';
$lang['SEARCH_INVALID_DATE'] = 'Ogiltigt datum angett för sökning';
$lang['SEARCH_INVALID_POSTCOUNT'] = 'Ogiltigt antal inlägg har angetts för att söka';
$lang['SEARCH_INVALID_USERFIELD'] = 'Ogiltiga Userfield data in';
$lang['SEARCH_INVALID_LASTVISITED'] = 'Ogiltigt datum angett för senast besökta sökning';
$lang['SEARCH_INVALID_LANGUAGE'] = 'Ogiltiga Valda Språket';
$lang['SEARCH_INVALID_TIMEZONE'] = 'Ogiltiga Vald Tidszon';
$lang['SEARCH_INVALID_MODERATORS'] = 'Ogiltiga Valda Forumet';
$lang['SEARCH_INVALID'] = 'Ogiltiga Sök';
$lang['SEARCH_INVALID_DAY'] = 'Den dagen du fyllde i var ogiltig';
$lang['SEARCH_INVALID_MONTH'] = 'Den månad du fyllde i var ogiltig';
$lang['SEARCH_INVALID_YEAR'] = 'Och med det år du fyllde i var ogiltig';
$lang['SEARCH_FOR_USERNAME'] = 'Att söka användarnamn matchande %s';
$lang['SEARCH_FOR_EMAIL'] = 'Söka e-post adresser som matchar %s';
$lang['SEARCH_FOR_IP'] = 'Att söka IP-adresser som matchar %s';
$lang['SEARCH_FOR_DATE'] = 'Söker användare som gått med i %s %d/%d/%d';
$lang['SEARCH_FOR_GROUP'] = 'Söka grupp medlemmar %s';
$lang['SEARCH_FOR_RANK'] = 'Söker bärare av rang %s';
$lang['SEARCH_FOR_BANNED'] = 'Söker bannad användare';
$lang['SEARCH_FOR_ADMINS'] = 'Söker Administratörer';
$lang['SEARCH_FOR_MODS'] = 'Söker Moderatorer';
$lang['SEARCH_FOR_DISABLED'] = 'Söker efter användare med funktionshinder';
$lang['SEARCH_FOR_POSTCOUNT_GREATER'] = 'Söker efter användare med ett inlägg som är större än %d';
$lang['SEARCH_FOR_POSTCOUNT_LESSER'] = 'Söker efter användare med ett inlägg räkna mindre än %d';
$lang['SEARCH_FOR_POSTCOUNT_RANGE'] = 'Söker efter användare med ett inlägg räkna mellan %d och %d';
$lang['SEARCH_FOR_POSTCOUNT_EQUALS'] = 'Söker efter användare med ett inlägg räkna värdet av %d';
$lang['SEARCH_FOR_USERFIELD_ICQ'] = 'Söker efter användare med en ICQ-adress som motsvarar %s';
$lang['SEARCH_FOR_USERFIELD_SKYPE'] = 'Söker efter användare med en Skype matchande %s';
$lang['SEARCH_FOR_USERFIELD_TWITTER'] = 'Söker efter användare med ett Twitter matchande %s';
$lang['SEARCH_FOR_USERFIELD_WEBSITE'] = 'Söker efter användare med en Webbplats som matchar %s';
$lang['SEARCH_FOR_USERFIELD_LOCATION'] = 'Söker efter användare med en Plats matchande %s';
$lang['SEARCH_FOR_USERFIELD_INTERESTS'] = 'Söker efter användare med deras Intressen fält matchande %s';
$lang['SEARCH_FOR_USERFIELD_OCCUPATION'] = 'Söker efter användare med sitt Yrke fält matchande %s';
$lang['SEARCH_FOR_LASTVISITED_INTHELAST'] = 'Searching for users who have visited in the last %s';
$lang['SEARCH_FOR_LASTVISITED_AFTERTHELAST'] = 'Searching for users who have visited after the last %s';
$lang['SEARCH_FOR_LANGUAGE'] = 'Söker efter användare som har satt %s som språk';
$lang['SEARCH_FOR_TIMEZONE'] = 'Söker efter användare som har satt UTC %s som sin tidszon';
$lang['SEARCH_FOR_STYLE'] = 'Söker efter användare som har satt %s som sin stil';
$lang['SEARCH_FOR_MODERATORS'] = 'Sök efter moderatorer på Forum -> %s';
$lang['SEARCH_USERS_ADVANCED'] = 'Avancerat Sök Användare';
$lang['SEARCH_USERS_EXPLAIN'] = 'Denna Modul gör det möjligt för dig att utföra avancerade sökningar för användare på ett stort antal kriterier. Läs beskrivningar under varje fält för att förstå varje sökalternativ helt.';
$lang['SEARCH_USERNAME_EXPLAIN'] = 'Här kan du göra en skillnad på gemener och versaler söka efter användarnamn. Om du vill matcha en del av användarnamn, så använd * (asterix) som jokertecken.';
$lang['SEARCH_EMAIL_EXPLAIN'] = 'Ange ett uttryck för att matcha användarens e-postadress. Detta är fallet okänslig. Om du vill göra en partiell match, använda * (asterisk som jokertecken.';
$lang['SEARCH_IP_EXPLAIN'] = 'Sök efter användare genom att en specifik IP-adress (xxx.xxx.xxx.xxx).';
$lang['SEARCH_USERS_JOINED'] = 'Användare som gått';
$lang['SEARCH_USERS_LASTVISITED'] = 'Användare som har besökt';
$lang['IN_THE_LAST'] = 'i det sista';
$lang['AFTER_THE_LAST'] = 'efter den sista';
$lang['BEFORE'] = 'Före';
$lang['AFTER'] = 'Efter';
$lang['SEARCH_USERS_JOINED_EXPLAIN'] = 'Sök efter användare ansluta sig Före eller Efter (och på) ett specifikt datum. Datumformatet är ÅÅÅÅ/MM/DD.';
$lang['SEARCH_USERS_GROUPS_EXPLAIN'] = 'Visa alla medlemmar i den valda gruppen.';
$lang['SEARCH_USERS_RANKS_EXPLAIN'] = 'Visa alla bärare av den valda rang.';
$lang['BANNED_USERS'] = 'Bannad Användare';
$lang['DISABLED_USERS'] = 'Funktionshindrade Användare';
$lang['SEARCH_USERS_MISC_EXPLAIN'] = 'Administratörer - Alla användare med administratörsbefogenheter; Moderatorer - Alla forummoderatorer; Förbjudna användare - Alla konton som har förbjudits på dessa forum; Funktionshindrade användare - Alla användare med inaktiverade konton (antingen manuellt inaktiverade eller aldrig verifierade sin e-postadress); Användare med inaktiverade PM:er - Väljer användare som har tagit bort privata meddelanden-privilegierna (Gjord via användarhantering)';
$lang['POSTCOUNT'] = 'Antal inlägg';
$lang['EQUALS'] = 'Lika med';
$lang['GREATER_THAN'] = 'Större än';
$lang['LESS_THAN'] = 'Mindre än';
$lang['SEARCH_USERS_POSTCOUNT_EXPLAIN'] = 'Du kan söka efter användare baserat på antalet inlägg. Du kan antingen söka efter ett specifikt värde, större än eller mindre än ett värde eller mellan två värden. För att göra intervallsökningen, välj "Lika med" och sätt sedan början och slutvärdena för intervallet åtskilda av ett bindestreck (-), t.ex. 10-15';
$lang['USERFIELD'] = 'Userfield';
$lang['SEARCH_USERS_USERFIELD_EXPLAIN'] = 'Söka efter användare baserat på olika fält i profilen. Jokertecken stöds med hjälp av en asterix (*).';
$lang['SEARCH_USERS_LASTVISITED_EXPLAIN'] = 'Du kan söka efter användare baserat på deras senaste logga in datum med hjälp av det här alternativet sök';
$lang['SEARCH_USERS_LANGUAGE_EXPLAIN'] = 'Detta kommer att visa användare som har valt ett visst språk i sin Profil';
$lang['SEARCH_USERS_TIMEZONE_EXPLAIN'] = 'Användare som har valt en viss tidszon i sin profil';
$lang['SEARCH_USERS_STYLE_EXPLAIN'] = 'Visa användare som har valt en specifik stil.';
$lang['MODERATORS_OF'] = 'Moderatorer';
$lang['SEARCH_USERS_MODERATORS_EXPLAIN'] = 'Sök efter användare med modereringsbehörighet till ett specifikt forum. Modereringsbehörigheter känns igen antingen av användarbehörigheter eller genom att vara i en grupp med rätt gruppbehörighet.';

$lang['SEARCH_USERS_NEW'] = '%s gav %d träff(ar). Utföra <a href="%s">another search</a>.';
$lang['BANNED'] = 'Förbjudna';
$lang['NOT_BANNED'] = 'Inte Förbjudas';
$lang['SEARCH_NO_RESULTS'] = 'Inga användare matchar dina valda kriterier. Vänligen försök en ny sökning. Om du söker användarnamn eller e-post adress fält för partiella matchningar måste du använda jokertecken * (asterix).';
$lang['ACCOUNT_STATUS'] = 'Status';
$lang['SORT_OPTIONS'] = 'Sortera alternativ:';
$lang['LAST_VISIT'] = 'Senaste Besök';
$lang['DAY'] = 'Dag';

$lang['POST_EDIT_CANNOT'] = 'Ledsen, men du kan inte redigera inlägg';
$lang['FORUMS_IN_CAT'] = 'forum i denna kategori';

$lang['MC_TITLE'] = 'Kommentarsmoderering';
$lang['MC_LEGEND'] = 'Skriv en kommentar';
$lang['MC_FAQ'] = 'Text kommer att visas under denna meddelande';
$lang['MC_COMMENT_PM_SUBJECT'] = "%s i ditt meddelande";
$lang['MC_COMMENT_PM_MSG'] = "Hej, [b]%s[/b]\nModerator kvar i ditt meddelande [url=%s][b]%s[/b][/url][quote]\n%s\n[/quote]";
$lang['MC_COMMENT'] = [
    0 => [
        'title' => '',
        'type' => 'Radera kommentar',
    ],
    1 => [
        'title' => 'Kommentar från %s',
        'type' => 'Kommentar',
    ],
    2 => [
        'title' => 'Information från %s',
        'type' => 'Information',
    ],
    3 => [
        'title' => 'Varning från %s',
        'type' => 'Varning',
    ],
    4 => [
        'title' => 'Överträdelse från %s',
        'type' => 'Kränkning',
    ],
];

$lang['SITEMAP'] = 'Sitemap';
$lang['SITEMAP_ADMIN'] = 'Hantera sitemap';
$lang['SITEMAP_CREATED'] = 'Sitemap skapas';
$lang['SITEMAP_AVAILABLE'] = 'och finns tillgänglig på';
$lang['SITEMAP_NOT_CREATED'] = 'Sitemap är ännu inte skapat';
$lang['SITEMAP_OPTIONS'] = 'Alternativ';
$lang['SITEMAP_CREATE'] = 'Skapa / uppdatera sitemap';
$lang['SITEMAP_WHAT_NEXT'] = 'Vad göra härnäst?';
$lang['SITEMAP_GOOGLE_1'] = 'Registrera din webbplats på <a href="https://www.google.com/webmasters/" target="_blank">Google Webmaster</a> med ditt Google-konto.';
$lang['SITEMAP_GOOGLE_2'] = '<a href="https://www.google.com/webmasters/tools/sitemap-list" target="_blank">Add sitemap</a> av webbplatsen att du registrerat dig.';
$lang['SITEMAP_YANDEX_1'] = 'Registrera din webbplats på <a href="https://webmaster.yandex.ru/sites/" target="_blank">Yandex Webmaster</a> med hjälp av ditt Google-konto.';
$lang['SITEMAP_YANDEX_2'] = '<a href="https://webmaster.yandex.ru/site/map.xml" target="_blank">Add sitemap</a> av webbplatsen att du registrerat dig.';
$lang['SITEMAP_BING_1'] = 'Registrera din webbplats på <a href="https://www.bing.com/webmaster/" target="_blank">Bing Webmaster</a> med ditt Microsoft-konto.';
$lang['SITEMAP_BING_2'] = 'Lägg till sajtkarta av webbplats du har registrerat i sina inställningar.';
$lang['SITEMAP_ADD_TITLE'] = 'Ytterligare sidor för sitemap';
$lang['SITEMAP_ADD_PAGE'] = 'Ytterligare sidor';
$lang['SITEMAP_ADD_EXP_1'] = 'Du kan ange ytterligare sidor på din webbplats som ska ingå i din sitemap-fil som du skapar.';
$lang['SITEMAP_ADD_EXP_2'] = 'Varje referens måste börja med http(s):// och en ny linje!';

$lang['FORUM_MAP'] = 'Forum\' karta';
$lang['ATOM_FEED'] = 'Foder';
$lang['ATOM_ERROR'] = 'Fel skapa foder';
$lang['ATOM_SUBSCRIBE'] = 'Prenumerera på feed';
$lang['ATOM_NO_MODE'] = 'No mode option provided for the feed';
$lang['ATOM_NO_FORUM'] = 'Detta forum inte har en feed (inga aktuella ämnen)';
$lang['ATOM_NO_USER'] = 'Den här användaren har inte en feed (inga aktuella ämnen)';
$lang['ATOM_UPDATED'] = 'Uppdaterad';
$lang['ATOM_GLOBAL_FEED'] = 'Globala feed för alla forum';

$lang['HASH_INVALID'] = 'Hash %s är ogiltig';
$lang['HASH_NOT_FOUND'] = 'Release med hash %s inte hittas';

$lang['TERMS_EMPTY_TEXT'] = '[align=center]The text of this page is edited at: [url]%s[/url]. This line can see only administrators.[/align]';
$lang['TERMS_EXPLAIN'] = 'På den här sidan kan du ange texten i de grundläggande reglerna för resursen visas för användare.';
$lang['TERMS_UPDATED_SUCCESSFULLY'] = 'Terms have been updated successfully';
$lang['CLICK_RETURN_TERMS_CONFIG'] = '%sClick Here to return to Terms editor%s';

$lang['TR_STATS'] = [
    0 => 'inaktiva användare i 30 dagar',
    1 => 'inaktiva användare för 90 dagar',
    2 => 'medium size distributions on the tracker',
    3 => 'hur många totalt händerna på tracker',
    4 => 'hur många lever händer (det är minst 1-lysdiod)',
    5 => 'hur många händer där som sådd mer än 5 frön',
    6 => 'hur många av oss uppladdare (de som fyllt minst 1 hand)',
    7 => 'hur många uppladdare under de senaste 30 dagarna',
];

$lang['NEW_POLL_START'] = 'Enkät aktiverad';
$lang['NEW_POLL_END'] = 'Omröstning avslutad';
$lang['NEW_POLL_ENDED'] = 'Denna enkät har redan avslutats';
$lang['NEW_POLL_DELETE'] = 'Enkät bort';
$lang['NEW_POLL_ADDED'] = 'Enkät läggas till';
$lang['NEW_POLL_ALREADY'] = 'Tema redan har en poll';
$lang['NEW_POLL_RESULTS'] = 'Enkät förändrats och gamla resultat borttagna';
$lang['NEW_POLL_VOTES'] = 'Du måste ange ett korrekt svar val (minimum 2, maximum är %s)';
$lang['NEW_POLL_DAYS'] = 'Tidpunkten för enkäten (%s dagar från tidpunkten för skapelsen tema) slutade redan';
$lang['NEW_POLL_U_NOSEL'] = 'Du har inte valt att rösta';
$lang['NEW_POLL_U_CHANGE'] = 'Redigera enkät';
$lang['NEW_POLL_U_EDIT'] = 'Ändra enkät (den gamla resultat kommer att raderas)';
$lang['NEW_POLL_U_VOTED'] = 'Alla röstade';
$lang['NEW_POLL_U_START'] = 'Aktivera undersökning';
$lang['NEW_POLL_U_END'] = 'Avsluta mätningen';
$lang['NEW_POLL_M_TITLE'] = 'Titel undersökning';
$lang['NEW_POLL_M_VOTES'] = 'Alternativ';
$lang['NEW_POLL_M_EXPLAIN'] = 'Varje rad motsvarar ett svar (max';

$lang['OLD_BROWSER'] = 'Du använder en för gammal webbläsare. Webbplatsen kommer inte att visas korrekt.';
$lang['GO_BACK'] = 'Gå tillbaka';

$lang['UPLOAD_ERROR_COMMON_DISABLED'] = 'File upload disabled';
$lang['UPLOAD_ERROR_COMMON'] = 'Ladda upp fil fel';
$lang['UPLOAD_ERROR_SIZE'] = 'Den uppladdade filen överskrider den maximala storleken av %s';
$lang['UPLOAD_ERROR_FORMAT'] = 'Ogiltig fil typ av bild';
$lang['UPLOAD_ERROR_DIMENSIONS'] = 'Image dimensions exceed the maximum allowable %sx%s pixels';
$lang['UPLOAD_ERROR_NOT_IMAGE'] = 'Den uppladdade filen är inte en bild';
$lang['UPLOAD_ERROR_NOT_ALLOWED'] = 'Förlängning %s för nedladdningar är inte tillåtna';
$lang['UPLOAD_ERRORS'] = [
    UPLOAD_ERR_INI_SIZE => 'du har överskridit det maximala filstorleken för server',
    UPLOAD_ERR_FORM_SIZE => 'du har överskridit det maximala ladda upp fil-storlek',
    UPLOAD_ERR_PARTIAL => 'filen var delvis hämtade',
    UPLOAD_ERR_NO_FILE => 'filen inte laddas upp',
    UPLOAD_ERR_NO_TMP_DIR => 'tillfälliga katalogen hittades inte',
    UPLOAD_ERR_CANT_WRITE => 'skriva fel',
    UPLOAD_ERR_EXTENSION => 'ladda upp stoppades av tillägg',
];

// Captcha
$lang['CAPTCHA'] = 'Kontrollera att du inte är en robot';
$lang['CAPTCHA_WRONG'] = 'Du kan inte bekräfta att du inte är en robot';
$lang['CAPTCHA_SETTINGS'] = '<h2>Captcha is not fully configured</h2><p>Generate the keys using the dashboard of your captcha service, after you need to put them at the file library/config.php.</p>';
$lang['CAPTCHA_OCCURS_BACKGROUND'] = 'The CAPTCHA verification occurs in the background';

// Sending email
$lang['REPLY_TO'] = 'Reply to';
$lang['EMAILER_SUBJECT'] = [
    'EMPTY' => 'Inget ämne',
    'GROUP_ADDED' => 'Du har lagts till i användargruppen',
    'GROUP_APPROVED' => 'Din begäran om att gå med i användargruppen har beviljats',
    'GROUP_REQUEST' => 'En begäran om att gå med i din användargrupp',
    'PRIVMSG_NOTIFY' => 'Nytt privat meddelande',
    'TOPIC_NOTIFY' => 'Notification of response in the thread - %s',
    'USER_ACTIVATE' => 'Återaktivering av konto',
    'USER_ACTIVATE_PASSWD' => 'Bekräftar ett nytt lösenord',
    'USER_WELCOME' => 'Välkommen till sajten %s',
    'USER_WELCOME_INACTIVE' => 'Välkommen till sajten %s',
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
