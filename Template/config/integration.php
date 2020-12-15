<h3><img src="<?= $this->url->dir() ?>plugins/Matrix/matrix-icon-32x32.png"/>&nbsp;Matrix</h3>
<div class="panel">
    <?= $this->form->label(t('Webhook URL'), 'matrix_webhook_url') ?>
    <?= $this->form->text('matrix_webhook_url', $values) ?>

    <p class="form-help"><a href="https://github.com/chriswep/kanboard-plugin-matrix" target="_blank"><?= t('Help on Matrix integration') ?></a></p>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue"/>
    </div>
</div>
