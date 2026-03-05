import { computed, ref } from 'vue'
import type { TwoFactorConfigContent } from '@/types'

export const useTwoFactorChallengePage = () => {
  const showRecoveryInput = ref<boolean>(false)
  const code = ref<string>('')

  const authConfigContent = computed<TwoFactorConfigContent>(() => {
    if (showRecoveryInput.value) {
      return {
        title: 'Recovery Code',
        description:
          'Please confirm access to your account by entering one of your emergency recovery codes.',
        buttonText: 'login using an authentication code',
      }
    }

    return {
      title: 'Authentication Code',
      description:
        'Enter the authentication code provided by your authenticator application.',
      buttonText: 'login using a recovery code',
    }
  })

  const toggleRecoveryMode = (clearErrors: () => void): void => {
    showRecoveryInput.value = !showRecoveryInput.value
    clearErrors()
    code.value = ''
  }

  return {
    authConfigContent,
    code,
    showRecoveryInput,
    toggleRecoveryMode,
  }
}
