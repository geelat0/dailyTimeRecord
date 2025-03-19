<script setup lang="ts">
</script>

<template>
  <footer
    class="group bg-background flex w-full items-center justify-center p-8 text-center opacity-90 hover:opacity-100 dark:text-white"
  >
    <div>
      <div class="order-2 grid grid-cols-1 gap-8 sm:order-1 sm:grid-cols-2">
        <!-- Logo Section -->
        <div class="flex flex-col gap-4 sm:gap-0 sm:text-left">
          <div class="flex items-center justify-center gap-4 sm:items-start sm:justify-normal">
            <img
              src="../../assets/images/dswd-fo-logo.png"
              alt="DSWD Logo"
              class="h-12 w-auto opacity-70 group-hover:opacity-100"
            />
            <img
              src="../../assets/images/bear-logo-dswd.png"
              alt="Empowerex Logo"
              class="h-12 w-auto opacity-70 group-hover:opacity-100"
            />
          </div>
          <div class="mt-2">
            <p
              class="text-foreground/80 leading-tight font-bold opacity-60 group-hover:opacity-100"
            >
              DSWD Field Office V
            </p>
            <p class="mt-auto text-sm leading-tight uppercase opacity-60 group-hover:opacity-100">
              <span class="">EMPOWEREX</span> 2024 &copy;
            </p>
            <p class="text-xs opacity-50 group-hover:opacity-100">Legazpi, Albay 4501</p>
          </div>
        </div>

        <!-- Sitemap Section -->
        <div>
          <div class="hidden">
            <p
              class="text-foreground/80 text-lg font-semibold uppercase opacity-60 group-hover:opacity-100"
            >
              Quick links
            </p>
            <ul>
              <li class="text-sm opacity-50 group-hover:opacity-100">
                <a href="/" class="hover:underline">Home</a>
              </li>
              <li class="text-sm opacity-50 group-hover:opacity-100">
                <a href="/profile" class="hover:underline">Profile</a>
              </li>
              <li class="text-sm opacity-50 group-hover:opacity-100">
                <a href="/ictms-service-desk" class="hover:underline">Request a Service</a>
              </li>
            </ul>
          </div>
          <div class="flex flex-col gap-2">
            <div class="text-center sm:text-right">
              <p
                class="text-foreground/80 text-lg font-semibold uppercase opacity-60 group-hover:opacity-100"
              >
                About Empowerex
              </p>
              <ul>
                <li class="text-sm opacity-50 group-hover:opacity-100">
                  <a href="/terms-and-conditions" class="hover:underline">Terms and Conditions</a>
                </li>
              </ul>
            </div>
            <div class="">
              <p
                class="text-foreground/80 text-center text-lg font-semibold uppercase opacity-60 group-hover:opacity-100 sm:text-right"
              >
                Social Platforms
              </p>
              <div>
                <ul class="flex flex-wrap items-center justify-center gap-2 sm:justify-end">
                  <li class="text-sm opacity-50 group-hover:opacity-100">
                    <a
                      href="https://www.facebook.com/dswdfo5bicol"
                      target="_blank"
                      class="hover:underline"
                      ><Icon icon="mdi:facebook" width="20"
                    /></a>
                  </li>
                  <li class="text-sm opacity-50 group-hover:opacity-100">
                    <a href="https://fo5.dswd.gov.ph/" target="_blank" class="hover:underline"
                      ><Icon icon="mdi:internet" width="20"
                    /></a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped></style>
