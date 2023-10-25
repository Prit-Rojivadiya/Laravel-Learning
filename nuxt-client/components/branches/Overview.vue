<template>
  <v-container grid-list-sm justify-space-around fluid class="p-4">
    <v-layout row>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Name:</span>
            <span>{{ branch.name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Account:</span>
            <span>{{ branch.client.name }}</span>
          </div>
        </div>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  name: "BranchOverview",
  props: ['value', 'branch'],
  data: () => ({
    working: false,
  }),
  async created () {
  },
}
</script>

<style scoped>
  .v-card .flex-item {
    margin-bottom: 5px;
  }
</style>
