<template>
  <v-container grid-list-sm justify-space-around fluid class="p-4">
    <v-layout row>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Name:</span>
            <span>{{ repair_order_type.name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Code:</span>
            <span>{{ repair_order_type.code }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Description:</span>
            <span>{{ repair_order_type.desc }}</span>
          </div>
        </div>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  name: "RepairOrderTypeOverview",
  props: ['value', 'repair_order_type'],
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
