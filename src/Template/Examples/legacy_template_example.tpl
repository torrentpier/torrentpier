<!DOCTYPE html>
<html>
<head>
    <title>{SITE_NAME} - {PAGE_TITLE}</title>
    <meta charset="utf-8">
    <!-- IF CUSTOM_META -->
    <meta name="description" content="{META_DESCRIPTION}">
    <!-- ENDIF -->
</head>
<body>
    <header>
        <h1>{L_WELCOME_MESSAGE}</h1>

        <!-- IF LOGGED_IN -->
            <div class="user-info">
                <p>Welcome back, {$userdata.username}!</p>
                <p>Last login: {$userdata.last_login}</p>
                <!-- IF IS_ADMIN -->
                    <a href="{U_ADMIN_PANEL}">{L_ADMIN_PANEL}</a>
                <!-- ENDIF -->
            </div>
        <!-- ELSE -->
            <div class="login-prompt">
                <a href="{U_LOGIN}">{L_LOGIN}</a> |
                <a href="{U_REGISTER}">{L_REGISTER}</a>
            </div>
        <!-- ENDIF -->
    </header>

    <main>
        <section class="news">
            <h2>{L_LATEST_NEWS}</h2>

            <!-- BEGIN news -->
                <article class="news-item">
                    <h3>{news.TITLE}</h3>
                    <div class="news-meta">
                        Posted by {news.AUTHOR} on {news.DATE}
                        <!-- IF news.COMMENTS_COUNT -->
                            | {news.COMMENTS_COUNT} comments
                        <!-- ENDIF -->
                    </div>
                    <div class="news-content">
                        {news.CONTENT}
                    </div>
                </article>
            <!-- END news -->
        </section>

        <section class="stats">
            <h3>{L_STATISTICS}</h3>
            <ul>
                <li>Total Users: {TOTAL_USERS}</li>
                <li>Active Torrents: {ACTIVE_TORRENTS}</li>
                <li>Total Downloads: {TOTAL_DOWNLOADS}</li>
                <li>Server Uptime: {#SERVER_START_TIME#}</li>
            </ul>
        </section>

        <!-- BEGIN categories -->
            <section class="category">
                <h3>{categories.NAME}</h3>

                <!-- BEGIN categories.torrents -->
                    <div class="torrent-item">
                        <a href="{categories.torrents.URL}">{categories.torrents.NAME}</a>
                        <span class="size">{categories.torrents.SIZE}</span>
                        <span class="seeders">{categories.torrents.SEEDERS}</span>
                        <span class="leechers">{categories.torrents.LEECHERS}</span>
                    </div>
                <!-- END categories.torrents -->
            </section>
        <!-- END categories -->
    </main>

    <!-- INCLUDE footer.tpl -->
</body>
</html>