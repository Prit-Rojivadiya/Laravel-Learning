<template>
  <v-container grid-list-sm justify-space-around fluid class="p-4">
    <v-layout row>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Fleet:</span><br>
            <span>{{ vehicle.fleet.name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Vehicle Unit Number:</span><br>
            <span>{{ vehicle.vehicle_number }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Year:</span><br>
            <span>{{ vehicle.year }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Make:</span><br>
            <span>{{ vehicle.make }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Model:</span><br>
            <span>{{ vehicle.model }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Vin #:</span><br>
            <span>{{ vehicle.vin }}</span>
          </div>
        </div>
      </v-flex>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">License Plate #:</span><br>
            <span>{{ vehicle.license_plate_number }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">License State:</span><br>
            <span class="title-case">{{ vehicle.license_state }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Purchase Price:</span><br>
            <span>{{ vehicle.purchase_price | formatMoney }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">In Service Date:</span><br>
            <span>{{ vehicle.in_service_date | formatDateMDY}}</span>
          </div>
        </div>
      </v-flex>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Vehicle Type:</span><br>
            <span>{{ vehicle.vehicle_type.name }}</span>
          </div>
        </div>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Engine Manufacturer:</span><br>
            <span>{{ vehicle_engine_manufacturer_name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Engine Serial #:</span><br>
            <span>{{ vehicle.engine_serial_number }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Tire Size:</span><br>
            <span>{{ vehicle.tire_size }}</span>
          </div>
        </div>
      </v-flex>

    </v-layout>
  </v-container>
</template>

<script>
export default {
  name: "VehicleOverview",
  props: ['value', 'vehicle'],
  data: () => ({
    working: false,
    vehicle_engine_manufacturer_name: null,
  }),
  async created () {
    if (this.vehicle && this.vehicle.engine_manufacturer) {
      this.vehicle_engine_manufacturer_name = this.vehicle.engine_manufacturer.name
    }
  },
}
</script>

<style scoped>
  .v-card .flex-item {
    margin-bottom: 5px;
  }
  .title-case {
    text-transform: capitalize;
  }
</style>
