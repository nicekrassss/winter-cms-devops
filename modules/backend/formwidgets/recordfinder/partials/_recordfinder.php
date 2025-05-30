<?php
$previewMode = false;
if ($this->previewMode || $field->readOnly) {
    $previewMode = true;
}
?>
<div
    id="<?= $this->getId() ?>"
    class="field-recordfinder loading-indicator-container size-input-text"
    data-control="recordfinder"
    data-refresh-handler="<?= $this->getEventHandler('onRefresh') ?>"
    data-data-locker="#<?= $field->getId() ?>">
    <span class="form-control"
            <?= $previewMode ? 'disabled="disabled"' : '' ?>
        <?php if (!$previewMode): ?>
            style="cursor:pointer"
            data-control="popup"
            data-size="huge"
            data-handler="<?= $this->getEventHandler('onFindRecord') ?>"
            data-request-data="recordfinder_flag: 1"
        <?php endif ?>
    >
        <?php if ($value): ?>
            <span class="primary"><?= e($nameValue) ?: 'Undefined' ?></span>
            <?php if ($descriptionValue): ?>
                <span class="secondary"> - <?= e($descriptionValue) ?></span>
            <?php endif ?>
        <?php else: ?>
            <span class="text-muted"><?= $prompt ?></span>
        <?php endif ?>
    </span>

    <?php if (!$previewMode): ?>
        <?php if ($value): ?>
            <button
                type="button"
                class="btn btn-default clear-record"
                data-request="<?= $this->getEventHandler('onClearRecord') ?>"
                data-request-confirm="<?= e(trans('backend::lang.form.action_confirm')) ?>"
                data-request-success="var $locker = $('#<?= $field->getId() ?>'); $locker.val(''); $locker.trigger('change')"
                aria-label="Remove">
                <i class="icon-times"></i>
            </button>
        <?php endif ?>
        <button
            id="<?= $this->getId('popupTrigger') ?>"
            class="btn btn-default find-record"
            data-control="popup"
            data-size="huge"
            data-handler="<?= $this->getEventHandler('onFindRecord') ?>"
            data-request-data="recordfinder_flag: 1"
            type="button">
            <i class="icon-th-list"></i>
        </button>
    <?php endif ?>

    <input
        type="hidden"
        name="<?= $field->getName() ?>"
        id="<?= $field->getId() ?>"
        value="<?= e($value) ?>"
        />
</div>
