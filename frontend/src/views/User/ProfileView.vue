<script setup>
import axios from 'axios'
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const loading = ref(true)
const saving = ref(false)
const form = ref({
    name: '',
    phone: '',
    email: '',
})

const loadProfile = async () => {
    loading.value = true
    try {
        const res = await axios.get('/user/profile')
        const profile = res.data?.data || {}
        form.value.name = profile.name || ''
        form.value.phone = profile.phone || ''
        form.value.email = profile.email || ''
    } catch (e) {
        toast.error(e.response?.data?.message || 'Failed to load profile')
    } finally {
        loading.value = false
    }
}

const saveProfile = async () => {
    saving.value = true
    try {
        await axios.put('/user/profile', {
            name: form.value.name,
            phone: form.value.phone,
        })
        toast.success('Profile updated successfully')
        router.push('/')
    } catch (e) {
        const errors = e.response?.data?.errors
        if (errors) {
            toast.error(Object.values(errors).flat().join(' '))
        } else {
            toast.error(e.response?.data?.message || 'Failed to update profile')
        }
    } finally {
        saving.value = false
    }
}

onMounted(async () => {
    await loadProfile()
    if (route.query.setup === '1') {
        toast.info('Please complete your profile to continue')
    }
})
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white py-14">
        <div class="max-w-2xl mx-auto px-6">
            <div class="bg-white rounded-3xl border border-gray-200 shadow-xl p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">My Profile</h1>
                <p class="text-gray-600 mb-8">Update your personal details.</p>

                <div v-if="loading" class="text-gray-600">Loading profile...</div>

                <form v-else @submit.prevent="saveProfile" class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Name</label>
                        <input v-model="form.name" type="text" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                        <input v-model="form.phone" type="text" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                        <input :value="form.email" type="email" disabled
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-500" />
                    </div>

                    <button type="submit" :disabled="saving"
                        class="w-full py-3.5 rounded-xl font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 disabled:opacity-60">
                        {{ saving ? 'Saving...' : 'Save Profile' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

