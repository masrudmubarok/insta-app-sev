import { tv } from 'tailwind-variants'
import { cva } from 'class-variance-authority'

export { default as NavigationMenu } from './NavigationMenu.vue'
export { default as NavigationMenuContent } from './NavigationMenuContent.vue'
export { default as NavigationMenuIndicator } from './NavigationMenuIndicator.vue'
export { default as NavigationMenuItem } from './NavigationMenuItem.vue'
export { default as NavigationMenuLink } from './NavigationMenuLink.vue'
export { default as NavigationMenuList } from './NavigationMenuList.vue'
export { default as NavigationMenuTrigger } from './NavigationMenuTrigger.vue'
export { default as NavigationMenuViewport } from './NavigationMenuViewport.vue'

export const navigationMenuTriggerStyle = tv({
  base: [
    'group inline-flex h-9 w-max items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition-colors',
    'bg-gradient-to-r from-[#4ECDC4] to-[#6C63FF] text-primary-foreground hover:from-[#3DBDB5] hover:to-[#5B52FF]',
    'focus:bg-gradient-to-r focus:from-[#3DBDB5] focus:to-[#5B52FF] focus:text-accent-foreground',
    'disabled:pointer-events-none disabled:opacity-50',
    'data-[active]:bg-gradient-to-r data-[active]:from-[#3DBDB5] data-[active]:to-[#5B52FF] data-[state=open]:bg-gradient-to-r data-[state=open]:from-[#3DBDB5] data-[state=open]:to-[#5B52FF]',
  ],
})
