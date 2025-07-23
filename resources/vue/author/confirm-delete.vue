<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3'
import Layout from '@/vue/layout.vue'

// Get props
const page = usePage()
const author = page.props.author as { id: number, name: string, email: string }

// Read page value from URL query (fallback to 1)
function getCurrentPage() {
  const params = new URLSearchParams(window.location.search)
  return Number(params.get("page")) || 1
}
const currentPage = getCurrentPage()

function confirmDelete() {
  // Add ?page=N to preserve context
  router.delete(`/authors/${author.id}/destroy?page=${currentPage}`, {
    onSuccess: () => {
      router.visit(`/authors?page=${currentPage}`)
    },
    onError: (err) => {
      console.error('Error:', err)
    }
  })
}

function cancelDelete() {
  router.visit(`/authors?page=${currentPage}`)
}
</script>

<template>
  <Layout title="Confirm Deletion">
    <div class="bg-white p-6 rounded shadow">
      <h2 class="text-2xl font-bold mb-4 text-red-600">Confirm Deletion</h2>
      <p class="mb-2">Are you sure you want to delete the following author?</p>
      <ul class="mb-4">
        <li><strong>Name:</strong> {{ author.name }}</li>
        <li><strong>Email:</strong> {{ author.email }}</li>
      </ul>
      <div class="flex gap-3">
        <button @click="confirmDelete" class="bg-red-600 text-white px-4 py-2 rounded">Yes, Delete</button>
        <button @click="cancelDelete" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
      </div>
    </div>
  </Layout>
</template>
