<template>
  <v-container grid-list-sm justify-space-around fluid class="p-4">
    <v-layout row>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Vehicle:</span>
            <span>{{ vehicle.vehicle_number }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Warranty Name:</span>
            <span>{{ warranty.name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Warranty Description:</span>
            <span>{{ warranty.desc }}</span>
          </div>
        </div>
      </v-flex>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Warranty Total Mileage:</span>
            <span>{{ warranty.mileage_total }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Vehicle Mileage Ends:</span>
            <span>{{ warranty.ending_mileage }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Date Warranty Ends:</span>
            <span>{{ warranty.ending_date | formatDateMDY}}</span>
          </div>
        </div>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  name: "WarrantyOverview",
  props: ['value', 'warranty'],
  data: () => ({
    working: false,
    vehicle: {
      vehicle_number: null
    },

  }),
  async created () {
    if (this.warranty && this.warranty.vehicle) {
      this.vehicle = this.warranty.vehicle
    }
  },
}
</script>

<style scoped>
  .v-card .flex-item {
    margin-bottom: 5px;
  }
</style>
