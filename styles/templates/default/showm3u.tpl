<!-- BEGIN m3ulist -->
{m3ulist.TITLE}
<!-- IF m3ulist.IS_VALID -->
<!-- IF m3ulist.IS_AUDIO -->
<audio class="player" src="{m3ulist.STREAM_LINK}" controls></audio><br>
<!-- ELSE -->
<video class="player" src="{m3ulist.STREAM_LINK}" playsinline controls></video>
<!-- ENDIF -->
<!-- ELSE -->
<a href="{m3ulist.STREAM_LINK}">DL</a>
<!-- ENDIF -->
<!-- END m3ulist -->
