<script setup lang="ts">
import { computed } from 'vue'
import { Button } from '@/Components/ui/button'
// import { useRouter } from 'vue-router'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuPortal,
  DropdownMenuSeparator,
  DropdownMenuSub,
  DropdownMenuSubContent,
  DropdownMenuSubTrigger,
  DropdownMenuTrigger
} from '@/Components/ui/dropdown-menu'

import { useColorMode } from '@vueuse/core'
import { Icon } from '@iconify/vue'

// Pass { disableTransition: false } to enable transitions
const mode = useColorMode({ disableTransition: false })
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" size="icon" class="rounded-full">
        <Icon icon="mdi:user-circle-outline" class="h-full w-full" width="35" />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent class="mt-2 min-w-56 p-2" align="end">
      <DropdownMenuLabel class="bg-accent flex rounded-md font-normal">
        <div>
          <div class="flex items-center justify-center gap-4">
            <Avatar class="" size="sm">
              <AvatarImage class="bg-accent cursor-pointer" />
              <AvatarFallback></AvatarFallback>
            </Avatar>
            <div class="flex flex-col space-y-1">
              <p class="leading-none font-semibold uppercase">
                
              </p>

              <p class="text-muted-foreground text-xs leading-none"></p>
              <p class="text-muted-foreground hidden text-xs uppercase">
               
              </p>
            </div>
          </div>
        </div>
      </DropdownMenuLabel>
      <DropdownMenuSeparator />
      <DropdownMenuGroup>
        <DropdownMenuItem>
          <User class="mr-2 h-4 w-4" />
          <span>Profile</span>
        </DropdownMenuItem>
        <DropdownMenuSub>
          <DropdownMenuSubTrigger>
            <Icon
              icon="radix-icons:moon"
              class="h-[1.2rem] w-[1.2rem] scale-100 rotate-0 transition-all dark:scale-0 dark:-rotate-90"
            />
            <Icon
              icon="radix-icons:sun"
              class="absolute h-[1.2rem] w-[1.2rem] scale-0 rotate-90 transition-all dark:scale-100 dark:rotate-0"
            />
            <span class="ml-2">Change Theme</span>
          </DropdownMenuSubTrigger>
          <DropdownMenuPortal>
            <DropdownMenuSubContent>
              <DropdownMenuItem @click="(mode = 'light'), trackAction('Light Theme')">
                Light
              </DropdownMenuItem>
              <DropdownMenuItem @click="(mode = 'dark'), trackAction('Dark Theme')">
                Dark
              </DropdownMenuItem>
              <DropdownMenuItem @click="(mode = 'auto'), trackAction('Auto Theme')">
                System
              </DropdownMenuItem>
            </DropdownMenuSubContent>
          </DropdownMenuPortal>
        </DropdownMenuSub>
      </DropdownMenuGroup>
      <DropdownMenuSeparator />
      <DropdownMenuItem>
        <LogOut class="mr-2 h-4 w-4" /> <span>Logout</span>
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>

<style scoped></style>
