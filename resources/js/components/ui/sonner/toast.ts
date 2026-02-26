import { reactive } from 'vue'

export type ToastVariant = 'default' | 'success' | 'error'

export type ToastOptions = {
  description?: string
  variant?: ToastVariant
  duration?: number
}

type ToastItem = {
  id: number
  title: string
  description?: string
  variant: ToastVariant
}

const toasts = reactive<ToastItem[]>([])

let seed = 0

const pushToast = (title: string, options: ToastOptions = {}) => {
  const id = ++seed

  toasts.push({
    id,
    title,
    description: options.description,
    variant: options.variant ?? 'default',
  })

  const duration = options.duration ?? 3000
  window.setTimeout(() => dismiss(id), duration)

  return id
}

export const dismiss = (id: number) => {
  const index = toasts.findIndex((toast) => toast.id === id)
  if (index !== -1) {
    toasts.splice(index, 1)
  }
}

type ToastFn = ((title: string, options?: ToastOptions) => number) & {
  success: (title: string, options?: Omit<ToastOptions, 'variant'>) => number
  error: (title: string, options?: Omit<ToastOptions, 'variant'>) => number
}

export const toast = ((title: string, options?: ToastOptions) => pushToast(title, options)) as ToastFn

toast.success = (title: string, options = {}) => pushToast(title, { ...options, variant: 'success' })
toast.error = (title: string, options = {}) => pushToast(title, { ...options, variant: 'error' })

export const toastStore = toasts
