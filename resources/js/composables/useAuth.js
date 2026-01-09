import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useAuth() {
    const page = usePage()

    const user = computed(() => page.props.auth.user)
    const userRole = computed(() => page.props.userRole)
    const isReadOnly = computed(() => page.props.isReadOnly)

    const isAdmin = computed(() => userRole.value === 'admin')
    const isStaff = computed(() => userRole.value === 'staff')
    const isAuditor = computed(() => userRole.value === 'auditor')

    return {
        user,
        userRole,
        isReadOnly,
        isAdmin,
        isStaff,
        isAuditor,
    }
}
