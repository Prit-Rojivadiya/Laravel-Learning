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
            <span class="grey--text">Meter Reading:</span>
            <span>{{ meterReading.meter_reading }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Date</span>
            <span>{{ meterReading.meter_reading_date  | formatDateMDYhhmmss }}</span>
          </div>
        </div>
      </v-flex>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Source:</span>
            <span>{{ meterReading.source }}</span>
          </div>
          <div class="flex-item" v-if="meterReading.location_state">
            <span class="grey--text">Location:</span>
            <span class="title-case">{{ meterReading.location_state | formatCapitalize }}, {{ meterReading.location_country }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Notes:</span>
            <span>{{ meterReading.notes }}</span>
          </div>
        </div>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  name: "MeterReadingOverview",
  props: ['value', 'meterReading'],
  data: () => ({
    working: false,
    vehicle: {
      vehicle_number: null
    },

  }),
  async created () {
    if (this.meterReading && this.meterReading.vehicle) {
      this.vehicle = this.meterReading.vehicle
    }
  },
}
</script>

<style scoped>
  .v-card .flex-item {
    margin-bottom: 5px;
  }
</style>
