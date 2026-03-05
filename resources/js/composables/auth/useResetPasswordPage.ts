import { ref } from 'vue'

export const useResetPasswordPage = (email: string) => {
  const inputEmail = ref(email)

  return {
    inputEmail,
  }
}
