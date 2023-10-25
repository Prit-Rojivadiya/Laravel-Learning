<template>
  <v-container grid-list-sm justify-space-around fluid class="p-4">
    <v-layout row>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Name:</span>
            <span>{{ vehicle_type.name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Type:</span>
            <span>{{ vehicle_type.system_vehicle_type.name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Description:</span>
            <span>{{ vehicle_type.desc }}</span>
          </div>
        </div>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  name: "VehicleTypeOverview",
  props: ['value', 'vehicle_type'],
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
