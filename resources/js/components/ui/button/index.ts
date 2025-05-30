import { tv } from 'tailwind-variants'

export { default as Button } from './Button.vue'

export const button = tv({
  base: 'inline-flex items-center justify-center rounded-md text-sm font-medium whitespace-nowrap ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
  variants: {
    variant: {
      default: 'bg-gradient-to-r from-[#4ECDC4] to-[#6C63FF] text-primary-foreground hover:from-[#3DBDB5] hover:to-[#5B52FF]',
      destructive:
        'bg-gradient-to-r from-[#FF4949] to-[#FF8C00] text-destructive-foreground hover:from-[#FF3333] hover:to-[#FF7A00]',
      outline:
        'border border-input bg-background hover:bg-accent hover:text-accent-foreground',
      secondary:
        'bg-gradient-to-r from-[#FFD93D] to-[#FF6B6B] text-secondary-foreground hover:from-[#FFC91A] hover:to-[#FF5252]',
      ghost: 'hover:bg-accent hover:text-accent-foreground',
      link: 'text-primary underline-offset-4 hover:underline',
    },
    size: {
      default: 'h-10 px-4 py-2',
      sm: 'h-9 rounded-md px-3',
      lg: 'h-11 rounded-md px-8',
      icon: 'h-10 w-10',
    },
  },
  defaultVariants: {
    variant: 'default',
    size: 'default',
  },
})
