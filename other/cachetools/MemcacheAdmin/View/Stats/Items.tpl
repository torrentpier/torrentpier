<?php
if((isset($response)) && ($response != array()))
{ ?>
    <div class="sub-header corner full-size padding">Result</div>
    <div class="container corner full-size padding">
    <?php
    foreach($response as $server => $result)
    { ?>
        <pre style="font-size:12px;overflow:auto;" class="full-size"><?php echo htmlentities(trim($result), ENT_NOQUOTES, 'UTF-8'); ?></pre>
    <?php
    }
    ?>
    </div>
<?php
} ?>
    <div class="sub-header corner full-size padding">
        Items in Slab <?php echo $_GET['slab']; ?>, only showing first <?php echo $_ini->get('max_item_dump'); ?> items
        <span style="float:right;"><a href="?server=<?php echo $_GET['server']; ?>&amp;show=slabs">Back to Server Slabs</a></span>
    </div>
    <div class="container corner full-size padding">
<?php
$notFirst = false;

# Items
foreach($items as $key => $data)
{
    # Checking if first item
    if($notFirst) { echo '<hr/>'; }
    ?>

        <a class="green item" href="index.php?server=<?php echo $_GET['server']; ?>&amp;show=items&amp;slab=<?php echo $_GET['slab']; ?>&amp;request_key=<?php echo $key; ?>&amp;request_api=<?php echo $_ini->get('get_api'); ?>&amp;request_command=get"><?php echo ((strlen($key) > 70) ? substr($key, 0, 70) . '[..]' : $key); ?></a>

        <span class="right" style="clear:right;">
            <strong>Size</strong> : <?php echo Library_Analysis::byteResize($data[0]); ?>Bytes,
            <strong>Expiration</strong> :
<?php
            # Infinite expiration
            if($data[1] == $infinite)
            {
                echo '&#8734;';
            }
            # Timestamp expiration
            else
            {
                echo Library_Analysis::uptime($data[1] - time());
            } ?>
        </span>
<?php
    # First item done
    $notFirst = true;
} ?>
    </div>