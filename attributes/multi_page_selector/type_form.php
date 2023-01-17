<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<fieldset class="ccm-attribute ccm-attribute-multipage">
    <legend><?php echo t('Restrictions')?></legend>

    <div class="form-group">
        <label>
            <?php echo $form->checkbox('akRestrictSingle', 1, isset($akRestrictSingle) ? $akRestrictSingle : '')?> <span><?php echo t('Single Page Selection Only')?></span>
        </label>
    </div>

    <div class="form-group">
        <label><?php echo t("Restrict to Pages Below")?></label>
        <?php echo $page_selector->selectPage('akParentID', (isset($akParentID) ? $akParentID : 1)); ?>
    </div>

    <div class="form-group">
        <label><?php echo t("Page Type")?></label>
        <select class="form-control" name="akPtID" id="akPtID">
            <option value="0">** <?php echo t('Any') ?> **</option>

            <?php if (is_array($pageTypeList)) {
                foreach ($pageTypeList as $ct) { ?>
                    <option value="<?php echo $ct->getPageTypeID() ?>" <?php if (isset($akPtID) && $akPtID == $ct->getPageTypeID()) { ?> selected <?php } ?>>
                        <?php echo $ct->getPageTypeDisplayName() ?>
                    </option>
                    <?php
                }
            }
            ?>
        </select>
    </div>




</fieldset>
