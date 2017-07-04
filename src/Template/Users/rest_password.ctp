<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">

    </ul>
</nav>
<div class="users form large-9 medium-8 columns content">
    <?= $this->Form->create() ?>
    <fieldset>
        <legend><?= __('Reset Password') ?></legend>
        <input type="hidden" name="token" id="token" value="<?= $data ?>">
        <input type="password" name="password" id="password" value="">
        <input type="password" name="c_password" id="c_password" value="">
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
