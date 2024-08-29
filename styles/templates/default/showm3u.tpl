<!-- BEGIN m3ulist -->
{m3ulist.TITLE}
<!-- IF m3ulist.IS_AUDIO -->
<audio class="player" src="{m3ulist.STREAM_LINK}" controls></audio><br>
<!-- ELSE -->
<video class="player" src="{m3ulist.STREAM_LINK}" playsinline controls></video>
<!-- ENDIF -->
<!-- END m3ulist -->
