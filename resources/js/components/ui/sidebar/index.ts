import type { VariantProps } from 'class-variance-authority'
import type { HTMLAttributes } from 'vue'
import { cva } from 'class-variance-authority'
import { tv } from 'tailwind-variants'

export interface SidebarProps {
  side?: 'left' | 'right'
  variant?: 'sidebar' | 'floating' | 'inset'
  collapsible?: 'offcanvas' | 'icon' | 'none'
  class?: HTMLAttributes['class']
}

export { default as Sidebar } from './Sidebar.vue'
export { default as SidebarContent } from './SidebarContent.vue'
export { default as SidebarFooter } from './SidebarFooter.vue'
export { default as SidebarGroup } from './SidebarGroup.vue'
export { default as SidebarGroupAction } from './SidebarGroupAction.vue'
export { default as SidebarGroupContent } from './SidebarGroupContent.vue'
export { default as SidebarGroupLabel } from './SidebarGroupLabel.vue'
export { default as SidebarHeader } from './SidebarHeader.vue'
export { default as SidebarInput } from './SidebarInput.vue'
export { default as SidebarInset } from './SidebarInset.vue'
export { default as SidebarMenu } from './SidebarMenu.vue'
export { default as SidebarMenuAction } from './SidebarMenuAction.vue'
export { default as SidebarMenuBadge } from './SidebarMenuBadge.vue'
export { default as SidebarMenuButton } from './SidebarMenuButton.vue'
export { default as SidebarMenuItem } from './SidebarMenuItem.vue'
export { default as SidebarMenuSkeleton } from './SidebarMenuSkeleton.vue'
export { default as SidebarMenuSub } from './SidebarMenuSub.vue'
export { default as SidebarMenuSubButton } from './SidebarMenuSubButton.vue'
export { default as SidebarMenuSubItem } from './SidebarMenuSubItem.vue'
export { default as SidebarProvider } from './SidebarProvider.vue'
export { default as SidebarRail } from './SidebarRail.vue'
export { default as SidebarSeparator } from './SidebarSeparator.vue'
export { default as SidebarTrigger } from './SidebarTrigger.vue'

export { useSidebar } from './utils'

export const sidebarMenuButtonVariants = tv({
  base: [
    'peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm outline-hidden ring-sidebar-ring transition-all duration-200',
    'bg-gradient-to-r hover:from-[#4ECDC4] hover:to-[#6C63FF] hover:text-primary-foreground',
    'focus-visible:ring-2 active:from-[#3DBDB5] active:to-[#5B52FF] active:text-primary-foreground',
    'disabled:pointer-events-none disabled:opacity-50',
    'group-has-data-[sidebar=menu-action]/menu-item:pr-8',
    'aria-disabled:pointer-events-none aria-disabled:opacity-50',
    'data-[active=true]:bg-gradient-to-r data-[active=true]:from-[#4ECDC4] data-[active=true]:to-[#6C63FF]',
    'data-[active=true]:font-medium data-[active=true]:text-primary-foreground',
    'data-[state=open]:bg-gradient-to-r data-[state=open]:from-[#4ECDC4] data-[state=open]:to-[#6C63FF]',
    'group-data-[collapsible=icon]:size-8! group-data-[collapsible=icon]:pr-2!',
    '[&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0',
  ],
  variants: {
    variant: {
      default: 'hover:bg-gradient-to-r hover:from-[#4ECDC4] hover:to-[#6C63FF]',
      outline:
        'border border-input bg-transparent hover:bg-gradient-to-r hover:from-[#4ECDC4] hover:to-[#6C63FF] hover:text-primary-foreground',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
})

export type SidebarMenuButtonVariants = VariantProps<typeof sidebarMenuButtonVariants>
