<template>
    <div class="software-update-page pb-5">
        <!-- Header & Breadcrumbs -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <i class="fas fa-sync-alt text-primary"></i>
                    Software & Database Update
                </h4>
                <p class="text-muted mb-0 small">
                    সফটওয়্যার কোড পরিবর্তনের পর নিজস্ব অর্গানাইজেশন ডাটাবেজ স্বয়ংক্রিয়ভাবে আপডেট ও সিনক্রোনাইজ করুন।
                </p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm px-3 d-flex align-items-center gap-2 shadow-sm" :disabled="loading || updating" @click="fetchStatus">
                    <i class="fas fa-redo" :class="{ 'fa-spin': loading }"></i>
                    <span>Check Again</span>
                </button>
            </div>
        </div>

        <!-- System & Database Overview Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-database fa-lg"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Organization DB</small>
                            <h6 class="fw-bold mb-0 text-dark font-monospace">{{ status.database_name || 'Loading...' }}</h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center" :class="status.is_update_needed ? 'bg-warning bg-opacity-10 text-warning' : 'bg-success bg-opacity-10 text-success'">
                            <i class="fas" :class="status.is_update_needed ? 'fa-exclamation-triangle fa-lg' : 'fa-check-circle fa-lg'"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Update Status</small>
                            <span v-if="status.is_update_needed" class="badge bg-warning text-dark px-2 py-1 fw-bold">
                                {{ status.pending_count }} Updates Pending
                            </span>
                            <span v-else class="badge bg-success px-2 py-1 fw-bold">
                                Up to Date
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info">
                            <i class="fas fa-layer-group fa-lg"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Migrations Applied</small>
                            <h6 class="fw-bold mb-0 text-dark">{{ status.executed_migrations || 0 }} / {{ status.total_migrations || 0 }}</h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center bg-secondary bg-opacity-10 text-secondary">
                            <i class="fab fa-laravel fa-lg"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Environment</small>
                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 13px;">Laravel {{ status.laravel_version }} (PHP {{ status.php_version }})</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Action & Status Section -->
        <div class="row g-4">
            <!-- Left Column: Update Card & Pending List -->
            <div class="col-lg-7">
                <!-- Update Pending State -->
                <div v-if="status.is_update_needed" class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-warning bg-opacity-10 border-warning border-opacity-25 py-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-bell text-warning fa-lg"></i>
                            <h6 class="fw-bold mb-0 text-dark">New Database Updates Available</h6>
                        </div>
                        <span class="badge bg-warning text-dark fw-bold px-3 py-1">Action Required</span>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-secondary mb-3">
                            কোডে নতুন ডাটাবেজ মাইগ্রেশন বা টেবিল পরিবর্তন পাওয়া গেছে যা আপনার অর্গানাইজেশনের ডাটাবেজে এখনও অ্যাপ্লাই করা হয়নি। নিচের বাটনে ক্লিক করে সাথে সাথে আপডেট করে নিন:
                        </p>

                        <!-- Action Buttons Grid -->
                        <div class="row g-2 mb-4">
                            <div class="col-md-7 col-12">
                                <button type="button" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow d-flex align-items-center justify-content-center gap-2 update-btn" :disabled="updating" @click="runUpdate">
                                    <i class="fas" :class="updating ? 'fa-spinner fa-spin' : 'fa-bolt'"></i>
                                    <span>{{ updating ? 'Processing... Please wait' : 'Update Database Now (ডাটাবেজ আপডেট)' }}</span>
                                </button>
                            </div>
                            <div class="col-md-5 col-12">
                                <button type="button" class="btn btn-outline-dark btn-lg w-100 fw-bold py-3 shadow-sm d-flex align-items-center justify-content-center gap-2" :disabled="updating" @click="syncMigrationsOnly" title="ম্যানুয়াল DB আপলোডের ক্ষেত্রে কোনো টেবিল চেঞ্জ না করে শুধু migrations টেবিলে রেকর্ড সিঙ্ক করবে">
                                    <i class="fas" :class="updating ? 'fa-spinner fa-spin' : 'fa-database'"></i>
                                    <span style="font-size: 13.5px;">Sync Migrations Table Only</span>
                                </button>
                            </div>
                            <div class="col-12 mt-1">
                                <small class="text-muted fst-italic d-block">
                                    <i class="fas fa-info-circle text-primary me-1"></i>
                                    <strong>টিপস:</strong> আপনি যদি phpMyAdmin-এ ম্যানুয়ালি লেটেস্ট DB ডাম্প আপলোড করে থাকেন, তবে <strong>"Sync Migrations Table Only"</strong> বাটনে ক্লিক করলেই পেন্ডিং আপডেটগুলো মাইগ্রেশন টেবিলে রেকর্ড হয়ে বাটন চলে যাবে।
                                </small>
                            </div>
                        </div>

                        <!-- Pending Migrations List -->
                        <div class="pending-items-box">
                            <h6 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                                <i class="fas fa-list-check text-primary"></i>
                                Pending Migrations & Alterations ({{ status.pending_count }})
                            </h6>
                            <div class="list-group list-group-flush border rounded-3 overflow-auto" style="max-height: 280px;">
                                <div v-for="(item, idx) in status.pending_migrations" :key="`mig_${idx}`" class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-file-code text-primary opacity-75"></i>
                                        <div>
                                            <div class="fw-semibold text-dark small">{{ item.title }}</div>
                                            <div class="text-muted font-monospace" style="font-size: 11px;">{{ item.name }}</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-light text-secondary border font-monospace" style="font-size: 10px;">{{ item.date }}</span>
                                </div>

                                <div v-for="(patch, idx) in status.pending_sql_patches" :key="`patch_${idx}`" class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 bg-info bg-opacity-10">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-file-lines text-info"></i>
                                        <div>
                                            <div class="fw-semibold text-dark small">{{ patch.title }}</div>
                                            <div class="text-muted font-monospace" style="font-size: 11px;">{{ patch.name }}</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-info text-white" style="font-size: 10px;">SQL Patch</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- All Up to Date State -->
                <div v-else class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
                    <div class="card-body p-5 text-center">
                        <div class="verified-icon-box mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success">
                            <i class="fas fa-check-double fa-3x"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Database is Completely Up to Date!</h4>
                        <p class="text-muted mx-auto mb-4" style="max-width: 480px;">
                            আপনার অর্গানাইজেশনের ডাটাবেজ লেটেস্ট সফটওয়্যার কোড ও স্কিমার সাথে শতভাগ সিনক্রোনাইজড রয়েছে। কোনো পেন্ডিং আপডেট নেই।
                        </p>
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <button type="button" class="btn btn-outline-primary px-4 py-2 fw-semibold rounded-pill d-flex align-items-center gap-2 shadow-sm" :disabled="loading" @click="fetchStatus">
                                <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
                                Check Again
                            </button>
                            <button type="button" class="btn btn-light px-4 py-2 fw-semibold rounded-pill d-flex align-items-center gap-2 border" :disabled="updating" @click="runUpdate">
                                <i class="fas fa-arrows-rotate"></i>
                                Force Re-sync (Run Migrations)
                            </button>
                            <button type="button" class="btn btn-outline-secondary px-4 py-2 fw-semibold rounded-pill d-flex align-items-center gap-2 border" :disabled="updating" @click="syncMigrationsOnly">
                                <i class="fas fa-database"></i>
                                Sync Migrations Table Only
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Terminal Console Output & Info -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white">
                    <div class="card-header bg-dark text-white py-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="terminal-dots d-flex gap-1">
                                <span class="dot bg-danger"></span>
                                <span class="dot bg-warning"></span>
                                <span class="dot bg-success"></span>
                            </div>
                            <span class="small fw-semibold ms-2 font-monospace">Execution Console Output</span>
                        </div>
                        <button v-if="consoleLogs" type="button" class="btn btn-sm btn-link text-white-50 text-decoration-none p-0" @click="consoleLogs = ''">
                            Clear
                        </button>
                    </div>
                    <div class="card-body p-3 bg-dark text-light font-monospace d-flex flex-column" style="min-height: 380px;">
                        <div class="console-box flex-grow-1 p-2 overflow-auto" style="max-height: 420px; font-size: 12px; line-height: 1.6;">
                            <div class="text-white-50 mb-2">// System ready. Click 'Update Database' or 'Sync Migrations Table Only' to execute.</div>
                            <div v-if="lastActionTime" class="text-info mb-2">[{{ lastActionTime }}] Console active.</div>
                            <pre class="m-0 text-success" style="white-space: pre-wrap; font-family: inherit;">{{ consoleLogs || '> Awaiting user command...' }}</pre>
                        </div>
                        <div class="border-top border-secondary pt-2 mt-2 d-flex justify-content-between align-items-center text-white-50 small" style="font-size: 11px;">
                            <span>Status: {{ updating ? 'Executing...' : (status.is_update_needed ? 'Pending' : 'Synchronized') }}</span>
                            <span v-if="status.last_migration">Latest: {{ status.last_migration }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "SoftwareUpdateIndex",
    data() {
        return {
            loading: false,
            updating: false,
            status: {
                is_update_needed: false,
                pending_count: 0,
                pending_migrations: [],
                pending_sql_patches: [],
                total_migrations: 0,
                executed_migrations: 0,
                last_migration: null,
                database_name: "",
                php_version: "",
                laravel_version: "",
            },
            consoleLogs: "",
            lastActionTime: "",
        };
    },
    created() {
        this.fetchStatus();
    },
    methods: {
        async fetchStatus() {
            this.loading = true;
            try {
                const res = await this.callApi("get", "software-update/status");
                if (res.status === 200) {
                    this.status = res.data;
                    this.lastActionTime = new Date().toLocaleTimeString();
                    // Update global state dynamically
                    if (this.$root.global) {
                        this.$root.global.db_update_needed = this.status.is_update_needed;
                        this.$root.global.pending_updates_count = this.status.pending_count;
                    }
                }
            } catch (err) {
                this.$toast("Failed to check update status.", "error");
            } finally {
                this.loading = false;
            }
        },

        async runUpdate() {
            this.updating = true;
            this.consoleLogs = "> Starting database update on tenant DB...\n> Checking and executing pending migrations and SQL patches...";
            this.lastActionTime = new Date().toLocaleTimeString();

            try {
                const res = await this.callApi("post", "software-update/run");
                if (res.status === 200 && res.data.success) {
                    this.consoleLogs = res.data.output || "All migrations completed successfully.";
                    this.$toast(res.data.message || "Database updated successfully!", "success");
                    
                    // Re-fetch updated status
                    await this.fetchStatus();
                } else {
                    this.consoleLogs = res.data.output || "Update failed.";
                    this.$toast(res.data.message || "Update encountered an error.", "error");
                }
            } catch (err) {
                this.consoleLogs += "\n> Error: Server returned an exception.";
                this.$toast("Database update execution failed.", "error");
            } finally {
                this.updating = false;
            }
        },

        async syncMigrationsOnly() {
            if (!confirm("Are you sure you want to mark all pending migrations as completed in the migrations table without executing DDL changes?")) {
                return;
            }
            this.updating = true;
            this.consoleLogs = "> Syncing migrations table records without executing DDL...\n> Baseline updating migrations table...";
            this.lastActionTime = new Date().toLocaleTimeString();

            try {
                const res = await this.callApi("post", "software-update/sync-only");
                if (res.status === 200 && res.data.success) {
                    this.consoleLogs = res.data.output || "Migrations table synced successfully.";
                    this.$toast(res.data.message || "Migrations table synced successfully!", "success");
                    
                    // Re-fetch updated status
                    await this.fetchStatus();
                } else {
                    this.consoleLogs = res.data.output || "Sync failed.";
                    this.$toast(res.data.message || "Sync encountered an error.", "error");
                }
            } catch (err) {
                this.consoleLogs += "\n> Error: Server returned an exception.";
                this.$toast("Sync execution failed.", "error");
            } finally {
                this.updating = false;
            }
        },
    },
};
</script>

<style scoped>
.stat-icon {
    width: 48px;
    height: 48px;
    flex-shrink: 0;
}
.verified-icon-box {
    width: 90px;
    height: 90px;
}
.update-btn {
    transition: all 0.3s ease;
}
.update-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(13, 110, 253, 0.35) !important;
}
.terminal-dots .dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}
.console-box::-webkit-scrollbar {
    width: 6px;
}
.console-box::-webkit-scrollbar-thumb {
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 4px;
}
</style>
