<?php /** @var \App\Modules\Settings\Models\SettingModel $s */ ?>
<div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10" x-data="githubUpdater()">
    <div class="border-b border-gray-200 dark:border-white/10 pb-4 mb-5 flex items-center justify-between flex-wrap gap-2">
        <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i data-lucide="git-pull-request" class="size-5 text-brand-500"></i> System Updates
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pull latest code updates directly from a private or public GitHub repository.</p>
        </div>
        <span class="inline-flex items-center rounded-md bg-gray-50 dark:bg-white/5 px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-400 ring-1 ring-inset ring-gray-500/10">
            Current Version: <span class="font-mono ml-1 font-bold text-brand-600 dark:text-brand-400"><?= esc(substr($s->get('github_version', 'N/A'), 0, 7)) ?></span>
        </span>
    </div>

    <form method="POST" action="<?= site_url('admin/settings/github-save') ?>" class="space-y-5">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <?= view('components/form/input',[
                'name'=>'github_repo','label'=>'GitHub Repository','required'=>true,'placeholder'=>'username/repo',
                'value'=>old('github_repo', $s->get('github_repo', 'Livezen-Technologies/saloncms')),
                'helpText'=>'Format: owner/repository'
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'github_branch','label'=>'Branch','required'=>true,'placeholder'=>'main',
                'value'=>old('github_branch', $s->get('github_branch', 'main')),
                'helpText'=>'Default: main'
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'github_token','label'=>'Personal Access Token (PAT)','placeholder'=>'ghp_xxxxxxxxxxxxxxxxxxxx',
                'type'=>'password',
                'value'=>old('github_token', $s->get('github_token') ? '••••••••••••••••••••••••' : ''),
                'helpText'=>'Required for private repositories'
            ]) ?>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4 pt-3 border-t border-gray-100 dark:border-white/5">
            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">
                    <i data-lucide="check" class="size-4"></i> Save Update Settings
                </button>
                <button type="button" @click="checkUpdates()" :disabled="checking" class="inline-flex items-center gap-1.5 rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/10 disabled:opacity-50">
                    <i data-lucide="refresh-cw" class="size-4" :class="checking && 'animate-spin'"></i> Check for Updates
                </button>
            </div>

            <div x-show="updateStatus" class="text-sm font-medium flex items-center gap-2" :class="updateStatus && updateStatus.up_to_date ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400'" style="display: none;">
                <span x-text="updateStatus && updateStatus.message"></span>
                <button type="button" x-show="updateStatus && !updateStatus.up_to_date" @click="applyUpdate()" :disabled="updating" class="inline-flex items-center gap-1 rounded-md bg-amber-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-500 disabled:opacity-50">
                    <i data-lucide="download" class="size-3.5"></i> Update to <span class="font-mono text-[10px]" x-text="updateStatus && updateStatus.latest_sha_short"></span>
                </button>
            </div>
        </div>
    </form>

    <!-- Status Details / Logs -->
    <div x-show="logOutput" class="mt-4 rounded-lg bg-gray-900 p-4 text-xs font-mono text-gray-300 border border-gray-800" style="display: none;">
        <div class="flex items-center justify-between border-b border-gray-800 pb-2 mb-2">
            <span class="text-gray-500 font-bold uppercase">Update Logs</span>
            <span class="size-2 rounded-full" :class="updating ? 'bg-amber-500 animate-ping' : (success ? 'bg-green-500' : 'bg-red-500')"></span>
        </div>
        <pre class="whitespace-pre-wrap leading-relaxed overflow-x-auto max-h-60" x-text="logOutput"></pre>
    </div>
</div>

<script>
window.githubUpdater = function() {
    return {
        checking: false,
        updating: false,
        success: false,
        updateStatus: null,
        logOutput: '',
        
        async checkUpdates() {
            this.checking = true;
            this.updateStatus = null;
            this.logOutput = '';
            try {
                const res = await fetch('<?= base_url("admin/settings/github-check") ?>');
                const data = await res.json();
                this.updateStatus = data;
                if (!data.success) {
                    this.logOutput = 'Error: ' + data.message;
                } else if (data.up_to_date) {
                    this.logOutput = `Checked: You are already on the latest version!\nCommit: ${data.latest_sha}\nAuthor: ${data.commit.author}\nDate: ${data.commit.date}\nMessage: ${data.commit.message}`;
                } else {
                    this.logOutput = `Update Available!\nLatest Commit: ${data.latest_sha}\nAuthor: ${data.commit.author}\nDate: ${data.commit.date}\nMessage: ${data.commit.message}\n\nClick "Update" to apply this release.`;
                }
            } catch (err) {
                this.logOutput = 'Failed to fetch update status: ' + err.message;
            } finally {
                this.checking = false;
                setTimeout(() => window.lucide && lucide.createIcons(), 50);
            }
        },
        
        async applyUpdate() {
            if (!confirm('Are you sure you want to perform this update? This will overwrite existing files (except .env, writable, vendor, and node_modules). Make sure you have backed up your database.')) {
                return;
            }
            this.updating = true;
            this.success = false;
            this.logOutput = 'Starting update process...\n';
            
            try {
                this.logOutput += '1. Initiating download from GitHub...\n';
                const res = await fetch('<?= base_url("admin/settings/github-update") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                    },
                    body: JSON.stringify({ sha: this.updateStatus.latest_sha })
                });
                
                const data = await res.json();
                this.logOutput += data.logs || '';
                if (data.success) {
                    this.success = true;
                    this.logOutput += '\n🎉 UPDATE SUCCESSFULLY APPLIED!\nPage will reload in 5 seconds...';
                    setTimeout(() => {
                        window.location.reload();
                    }, 5000);
                } else {
                    this.logOutput += '\n❌ Update Failed: ' + data.message;
                }
            } catch (err) {
                this.logOutput += '\n❌ Error: ' + err.message;
            } finally {
                this.updating = false;
            }
        }
    };
};
</script>
