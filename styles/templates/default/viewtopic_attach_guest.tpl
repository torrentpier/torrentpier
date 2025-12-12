<div>
    <fieldset class="attach">
        <legend>{L_DOWNLOAD}</legend>
        <h1 class="attach_link"><a href="{U_REGISTER}" style="color: brown;">{L_DOWNLOAD_INFO}</a></h1>
        <p id="guest-dl-tip" class="attach_comment med">
            <a href="{#FILELIST_URL#}{TOPIC_ID}/files/" target="_blank" class="med"><b>{L_BT_FLIST_LINK_TITLE}</b></a> &middot;
            <a href="{{ config('how_to_download_url_help') }}" class="med"><b>{L_HOW_TO_DOWNLOAD}</b></a> &middot;
            <a href="{{ config('what_is_torrent_url_help') }}" class="med"><b>{L_WHAT_IS_A_TORRENT}</b></a>
            <!-- IF #RATIO_ENABLED -->
            &middot; <a href="{{ config('ratio_url_help') }}" class="med"><b>{L_RATINGS_AND_LIMITATIONS}</b></a>
            <!-- ENDIF --><br/>
        </p>
    </fieldset>
</div>
