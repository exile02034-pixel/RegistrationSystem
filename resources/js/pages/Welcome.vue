<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { dashboard, login } from '@/routes'

withDefaults(
  defineProps<{
    canRegister: boolean
  }>(),
  { canRegister: true },
)
</script>

<template>
  <Head title="Welcome">
    <link rel="preconnect" href="https://rsms.me/" />
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
  </Head>

  <div class="min-h-screen bg-[#F8FAFC] text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]">
    <!-- Top Nav -->
    <header class="mx-auto w-full max-w-6xl px-6 pt-6">
      <nav
        class="flex items-center justify-between rounded-2xl border border-[#E2E8F0] bg-white/70 px-4 py-3 backdrop-blur dark:border-[#1E3A5F] dark:bg-[#0F2747]/40"
      >
        <!-- Brand -->
        <div class="flex items-center gap-3">
          <div class="grid h-9 w-9 place-items-center rounded-xl bg-[#2563EB] text-white shadow-sm">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M7 7.5A3.5 3.5 0 0 1 10.5 4h3A3.5 3.5 0 0 1 17 7.5V8h1a2 2 0 0 1 2 2v7a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-7a2 2 0 0 1 2-2h1v-.5Z"
                stroke="currentColor"
                stroke-width="1.8"
              />
              <path d="M8 8h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
            </svg>
          </div>

          <div class="leading-tight">
            <p class="text-sm font-semibold">Registration System</p>
            <p class="text-xs text-[#475569] dark:text-[#9FB3C8]">Client & Admin Document Portal</p>
          </div>
        </div>

        <!-- Links -->
        <div class="flex items-center gap-2">
          <Link
            v-if="$page.props.auth.user"
            :href="dashboard()"
            class="rounded-xl bg-[#2563EB] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#1D4ED8] dark:hover:bg-[#3B82F6]"
          >
            Go to Dashboard
          </Link>

          <template v-else>
            <Link
              :href="login()"
              class="rounded-xl bg-[#2563EB] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#1D4ED8] dark:hover:bg-[#3B82F6]"
            >
              Log in
            </Link>
          </template>
        </div>
      </nav>
    </header>

    <!-- Hero -->
    <main class="mx-auto w-full max-w-6xl px-6 pb-16 pt-10">
      <div class="grid gap-8 lg:grid-cols-2 lg:items-center">
        <!-- Left: Copy -->
        <section>
          <div
            class="inline-flex items-center gap-2 rounded-full border border-[#E2E8F0] bg-white px-3 py-1 text-xs text-[#475569] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#9FB3C8]"
          >
            <span class="h-2 w-2 rounded-full bg-[#60A5FA]"></span>
            Secure • Organized • Fast Access
          </div>

          <h1 class="mt-4 text-3xl font-semibold tracking-tight lg:text-5xl">
            Simple registration workflow with a clean document portal.
          </h1>

          <p class="mt-4 max-w-xl text-base leading-relaxed text-[#475569] dark:text-[#9FB3C8]">
            Clients can view and download released documents anytime. Admins can upload, edit, organize, and manage
            documents—fast and easy, like a simple Drive experience.
          </p>

          <div class="mt-6 flex flex-wrap items-center gap-3">
            <Link
              v-if="$page.props.auth.user"
              :href="dashboard()"
              class="rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] dark:hover:bg-[#3B82F6]"
            >
              Open Dashboard
            </Link>

            <Link
              v-else
              :href="login()"
              class="rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] dark:hover:bg-[#3B82F6]"
            >
              Get Started
            </Link>

            <span class="text-xs text-[#475569] dark:text-[#9FB3C8]">
              Use your school/company account to log in.
            </span>
          </div>

          <!-- Feature bullets -->
          <ul class="mt-8 grid gap-3 text-sm text-[#475569] dark:text-[#9FB3C8]">
            <li class="flex items-start gap-3">
              <span
                class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-lg bg-[#60A5FA]/25 text-[#2563EB] dark:bg-[#2563EB]/20 dark:text-[#60A5FA]"
              >
                ✓
              </span>
              Clients see only <span class="font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">Released</span> documents.
            </li>
            <li class="flex items-start gap-3">
              <span
                class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-lg bg-[#60A5FA]/25 text-[#2563EB] dark:bg-[#2563EB]/20 dark:text-[#60A5FA]"
              >
                ✓
              </span>
              Admins can <span class="font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">upload, edit, delete</span>, and
              organize by category.
            </li>
            <li class="flex items-start gap-3">
              <span
                class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-lg bg-[#60A5FA]/25 text-[#2563EB] dark:bg-[#2563EB]/20 dark:text-[#60A5FA]"
              >
                ✓
              </span>
              Clean tables, search, and filters—optimized for fast document access.
            </li>
          </ul>
        </section>

        <!-- Right: Visual mock panel -->
        <section
          class="relative overflow-hidden rounded-3xl border border-[#E2E8F0] bg-white shadow-sm dark:border-[#1E3A5F] dark:bg-[#0B1F3A]"
        >
          <div
            class="absolute inset-0 opacity-60"
            :style="{
              background:
                'radial-gradient(800px circle at 20% 10%, rgba(96,165,250,0.35), transparent 45%), radial-gradient(700px circle at 80% 80%, rgba(37,99,235,0.25), transparent 55%)'
            }"
          />

          <div class="relative p-6">
            <!-- Fake toolbar -->
            <div
              class="flex items-center justify-between rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] p-3 dark:border-[#1E3A5F] dark:bg-[#0F2747]"
            >
              <div class="flex items-center gap-2">
                <div class="h-8 w-8 rounded-xl bg-[#2563EB]"></div>
                <div>
                  <p class="text-sm font-semibold">My Documents</p>
                  <p class="text-xs text-[#475569] dark:text-[#9FB3C8]">Client View</p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <div
                  class="h-9 w-28 rounded-xl border border-[#E2E8F0] bg-white dark:border-[#1E3A5F] dark:bg-[#12325B]"
                ></div>
                <div class="h-9 w-9 rounded-xl bg-[#2563EB]"></div>
              </div>
            </div>

            <!-- Fake table -->
            <div class="mt-4 overflow-hidden rounded-2xl border border-[#E2E8F0] dark:border-[#1E3A5F]">
              <div
                class="grid grid-cols-12 bg-[#F8FAFC] px-4 py-3 text-xs font-semibold text-[#475569] dark:bg-[#0F2747] dark:text-[#9FB3C8]"
              >
                <div class="col-span-6">Name</div>
                <div class="col-span-3">Modified</div>
                <div class="col-span-2">Status</div>
                <div class="col-span-1 text-right">⋯</div>
              </div>

              <div class="divide-y divide-[#E2E8F0] bg-white dark:divide-[#1E3A5F] dark:bg-[#12325B]">
                <div class="grid grid-cols-12 items-center px-4 py-3">
                  <div class="col-span-6 flex items-center gap-3">
                    <div class="h-9 w-9 rounded-xl bg-[#60A5FA]/30 dark:bg-[#2563EB]/25"></div>
                    <div>
                      <p class="text-sm font-semibold">Registration Form.pdf</p>
                      <p class="text-xs text-[#475569] dark:text-[#9FB3C8]">PDF • 1.2 MB</p>
                    </div>
                  </div>
                  <div class="col-span-3 text-sm text-[#475569] dark:text-[#9FB3C8]">Feb 25, 2026</div>
                  <div class="col-span-2">
                    <span
                      class="rounded-full bg-[#60A5FA]/25 px-2 py-1 text-xs font-semibold text-[#2563EB] dark:bg-[#2563EB]/25 dark:text-[#60A5FA]"
                    >
                      Released
                    </span>
                  </div>
                  <div class="col-span-1 text-right">
                    <span class="inline-flex rounded-xl bg-[#2563EB] px-3 py-1.5 text-xs font-semibold text-white">
                      Download
                    </span>
                  </div>
                </div>

                <div class="grid grid-cols-12 items-center px-4 py-3">
                  <div class="col-span-6 flex items-center gap-3">
                    <div class="h-9 w-9 rounded-xl bg-[#60A5FA]/30 dark:bg-[#2563EB]/25"></div>
                    <div>
                      <p class="text-sm font-semibold">Proof of Payment.png</p>
                      <p class="text-xs text-[#475569] dark:text-[#9FB3C8]">PNG • 420 KB</p>
                    </div>
                  </div>
                  <div class="col-span-3 text-sm text-[#475569] dark:text-[#9FB3C8]">Feb 10, 2026</div>
                  <div class="col-span-2">
                    <span
                      class="rounded-full bg-[#E2E8F0] px-2 py-1 text-xs font-semibold text-[#475569] dark:bg-[#0F2747] dark:text-[#9FB3C8]"
                    >
                      Draft
                    </span>
                  </div>
                  <div class="col-span-1 text-right">
                    <span
                      class="inline-flex rounded-xl border border-[#E2E8F0] bg-white px-3 py-1.5 text-xs font-semibold text-[#0B1F3A] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF]"
                    >
                      View
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Admin mini card -->
            <div
              class="mt-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] p-4 dark:border-[#1E3A5F] dark:bg-[#0F2747]"
            >
              <p class="text-sm font-semibold">Admin Controls</p>
              <p class="mt-1 text-xs text-[#475569] dark:text-[#9FB3C8]">
                Upload • Edit details • Release to client • Delete (with confirmation)
              </p>
              <div class="mt-3 flex gap-2">
                <div class="h-9 flex-1 rounded-xl bg-[#2563EB]"></div>
                <div
                  class="h-9 w-20 rounded-xl border border-[#E2E8F0] bg-white dark:border-[#1E3A5F] dark:bg-[#12325B]"
                ></div>
              </div>
            </div>
          </div>
        </section>
      </div>

      <!-- Footer -->
      <footer class="mt-12 text-center text-xs text-[#475569] dark:text-[#9FB3C8]">
        © {{ new Date().getFullYear() }} Registration System • Built with Laravel + Vue
      </footer>
    </main>
  </div>
</template>
