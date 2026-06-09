<?php
$isEdit = !empty($row);
$action = $isEdit ? site_url('admin/services/' . $row['id']) : site_url('admin/services');
$catOpts = ['' => '— None —'];
foreach ($categories as $c) $catOpts[$c['id']] = $c['name'];
?>
<form method="POST" action="<?= $action ?>" class="max-w-2xl space-y-6">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>

    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10 space-y-5">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white"><?= $isEdit ? 'Edit' : 'New' ?> Service</h3>
        <?= view('components/form/input', ['name'=>'name','label'=>'Service name','required'=>true,'value'=>$row['name'] ?? '']) ?>
        <?= view('components/form/select', ['name'=>'category_id','label'=>'Category','selected'=>$row['category_id'] ?? '','options'=>$catOpts]) ?>
        <div class="grid grid-cols-3 gap-4">
            <?= view('components/form/input', ['name'=>'duration_min','label'=>'Duration (min)','type'=>'number','required'=>true,'value'=>$row['duration_min'] ?? '30']) ?>
            <?= view('components/form/input', ['name'=>'price','label'=>'Price','type'=>'number','required'=>true,'value'=>$row['price'] ?? '0','attrs'=>['step'=>'0.01']]) ?>
            <?= view('components/form/input', ['name'=>'tax_pct','label'=>'Tax %','type'=>'number','value'=>$row['tax_pct'] ?? '0','attrs'=>['step'=>'0.01']]) ?>
        </div>
        <?= view('components/form/textarea', ['name'=>'description','label'=>'Description','rows'=>3,'value'=>$row['description'] ?? '']) ?>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" <?= !$isEdit || !empty($row['is_active']) ? 'checked' : '' ?> class="rounded border-gray-300 dark:border-white/10 text-brand-600 dark:text-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400">
            Active (visible for booking)
        </label>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="<?= site_url('admin/services') ?>" class="rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 ring-1 ring-gray-300 dark:ring-white/10 hover:bg-gray-50 dark:hover:bg-white/5 dark:bg-white/5">Cancel</a>
        <button type="submit" class="rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700"><?= $isEdit ? 'Save' : 'Create' ?></button>
    </div>
</form>
