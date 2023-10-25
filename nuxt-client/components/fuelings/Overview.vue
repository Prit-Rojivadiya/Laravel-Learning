<template>
  <v-container grid-list-sm justify-space-around fluid class="p-4">
    <v-layout row>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Vehicle Unit Number:</span>
            <span>{{ fueling.vehicle.vehicle_number }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Vendor:</span>
            <span>{{ vendor_name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Meter Reading:</span>
            <span>{{ fueling.meter_reading }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Price Per Unit:</span>
            <span>{{ fueling.price_per_unit | formatMoney }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Location State:</span>
            <span class="title-case">{{ fueling.location_state | formatCapitalize}}, {{ fueling.location_country }}</span>
          </div>
        </div>
      </v-flex>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Fueling Date:</span>
            <span>{{ fueling.fueling_date | formatDateMDY }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Total Price:</span>
            <span>{{ fueling.total_price | formatMoney }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Fuel Type:</span>
            <span>{{ fuel_type_name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Total Units:</span>
            <span>{{ fueling.total_units | formatMoneyNoSign }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Fuel Unit:</span>
            <span>{{ fuel_unit_type_name }}</span>
          </div>
        </div>
      </v-flex>
    </v-layout>
    <v-layout row>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Notes:</span>
            <span>{{ fueling.notes }}</span>
          </div>
        </div>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  name: "FuelingOverview",
  props: ['value', 'fueling'],
  data: () => ({
    working: false,
    vendor_name: null,
    fuel_unit_type_name: null,
    fuel_type_name: null,
  }),
  async created () {
    if (this.fueling.vendor) {
      this.vendor_name = this.fueling.vendor.name
    }
    if (this.fueling.fuel_unit_type) {
      this.fuel_unit_type_name = this.fueling.fuel_unit_type.name
    }
    if (this.fueling.fuel_type) {
      this.fuel_type_name = this.fueling.fuel_type.name
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
