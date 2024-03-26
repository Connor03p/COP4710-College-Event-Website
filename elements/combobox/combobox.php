<link rel="stylesheet" href="/Elements/combobox/combobox.css">
<div class="combobox combobox-list">
    <div class="group">
        <input id="<?=$combobox_id?>" name="<?=$combobox_name?>" placeholder="<?=$combobox_placeholder?>" class="cb_edit" type="text" role="combobox" aria-autocomplete="both" aria-expanded="false" aria-controls="<?=$combobox_name?>-listbox" required>
        <span></span>
    </div>
    <ul id="<?=$combobox_name?>-listbox" role="listbox" aria-label="Options">
        <?php
            while ($option = mysqli_fetch_array($combobox_options,MYSQLI_ASSOC)):;
            $option_name = $option["name"];
        ?>
        <li role="option" tabindex="-1" aria-selected="false" data-value="<?php echo $option_name;?>"><?php echo $option_name;?></li>
        <?php
            endwhile;
        ?>
    </ul>
</div>
<script src="/Elements/combobox/combobox.js"></script>
