<!-- BEGIN c -->
    ..body.. {c.U_VIEWCAT} ..body..
    <!-- BEGIN f -->
        <!-- IF c.f.FORUM_DESC -->
        {c.f.FORUM_DESC}
        <!-- ENDIF -->
        <!-- BEGIN sf -->
        ..body.. {c.f.sf.SF_NAME} ..body..
        <!-- END sf -->
    <!-- END f -->
<!-- END c -->

<script>
    var $jsVar = $('#tag').length;
</script>
