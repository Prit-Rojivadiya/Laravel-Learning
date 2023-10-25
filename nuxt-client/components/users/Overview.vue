<template>
  <v-container grid-list-sm justify-space-around fluid class="p-4">
    <v-layout row>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Tenant:</span>
            <span>{{ user.tenant }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Name:</span>
            <span>{{ user.name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Email:</span>
            <span>{{ user.email }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Role:</span>
            <span>{{ userRoles }}</span>
          </div>
        </div>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  name: "UserOverview",
  props: ['value', 'user'],
  data: () => ({
    working: false,
    userRoles: null
  }),
  created () {
    this.getRole()
  },
  methods: {
    async getRole () {
      if (!this.user) {
        this.userRoles = 'none'
      }
      const response = await this.$axios.$get(`/users/role/${this.user.id}`)
      if (response.length > 0) {
        this.userRoles = response[0].roleName
      }
      else {
        this.userRoles = 'none'
      }
      this.userRoles = this.userRoles.replaceAll('-', ' ')
    }
  }
}
</script>

<style scoped>
  .v-card .flex-item {
    margin-bottom: 5px;
  }
</style>
