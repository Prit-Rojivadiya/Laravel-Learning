<template>
  <v-dialog
    :value="value"
    @input="$emit('input')"
    fullscreen
    hide-overlay
    transition="dialog-bottom-transition"
  >
    <v-card>
      <v-toolbar dark color="primary">
        <v-btn icon dark @click="$emit('input')">
          <v-icon>mdi-close</v-icon>
        </v-btn>
        <v-toolbar-title>
          {{ vehicle && vehicle.id ? 'Edit' : 'New' }} Vehicle
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn dark text :loading="working" @click="saveVehicle()">
            {{ vehicle && vehicle.id ? 'Save' : 'Create' }} Vehicle
          </v-btn>
        </v-toolbar-items>
      </v-toolbar>

      <v-alert v-if="errors.length" class="error">
        <template v-for="error in errors">
          {{ error.msg }}<br />
        </template>
        Please try again
      </v-alert>

      <v-form ref="form" v-model="formValid">
        <div class="overline px-4 pt-4 pb-2">Details</div>
        <v-container grid-list-sm justify-space-around fluid class="p-4">
          <v-layout row>
            <v-flex>
              <div class="flex-content">
                <div class="flex-item tz-form-item">
                  <v-select
                    v-model="form.fleet_id"
                    :rules="[v => !!v || 'Please select the fleet this vehicle belongs to']"
                    :items="fleets"
                    item-value="id"
                    item-text="name"
                    label="Fleet"
                    required
                    dense
                  />
                </div>
                <div class="flex-item tz-form-item">
                  <v-text-field
                    v-model="form.vehicle_number"
                    :rules="[v => !!v || 'Please enter a unique vehicle unit number or abbreviation for this vehicle']"
                    label="Vehicle Unit Number"
                    required
                    dense
                  />
                </div>
                <div class="flex-item tz-form-item">
                  <v-text-field v-model="form.year" label="Year" dense/>
                </div>
                <div class="flex-item tz-form-item">
                  <v-text-field v-model="form.make" label="Make" dense/>
                </div>
                <div class="flex-item tz-form-item">
                  <v-text-field v-model="form.model" label="Model" dense/>
                </div>
                <div class="flex-item tz-form-item">
                  <v-text-field v-model="form.vin" label="Vin #" dense/>
                </div>
              </div>
            </v-flex>
            <v-flex>
              <div class="flex-content">
                <div class="flex-item tz-form-item">
                  <v-text-field v-model="form.license_plate_number" label="License Plate #" dense/>
                </div>
                <div class="flex-item tz-form-item">
                  <StateSelector
                    v-model="form.license_state"
                    label="License State"
                  />
                </div>
                <div class="flex-item tz-form-item">
                  <v-text-field v-model="form.purchase_price" label="Purchase Price" dense/>
                </div>
                <div class="flex-item tz-form-item">
                  <DatePicker
                    v-if="showDates"
                    v-model="form.in_service_date"
                    label="In Service Date"
                    @input="refresh"
                  />
                </div>
              </div>
            </v-flex>
            <v-flex>
              <div class="flex-content">
                <div class="flex-item tz-form-item">
                  <v-select
                    v-model="form.vehicle_type_id"
                    :rules="[v => !!v || 'Please select a vehicle type for this vehicle']"
                    :items="vehicle_types"
                    item-value="id"
                    item-text="name"
                    label="Vehicle Type"
                    required
                    dense
                  />
                </div>
              </div>
              <div class="flex-content">
                <div class="flex-item tz-form-item">
                  <v-select
                    v-model="form.engine_manufacturer_id"
                    :items="engine_manufacturers"
                    item-value="id"
                    item-text="name"
                    label="Engine Manufacturer"
                    dense
                  />
                </div>
              </div>
              <div class="flex-item tz-form-item">
                <v-text-field v-model="form.engine_serial_number" label="Engine Serial #" dense/>
              </div>
              <div class="flex-item tz-form-item">
                <v-text-field v-model="form.tire_size" label="Tire Size" dense/>
              </div>
            </v-flex>

          </v-layout>
        </v-container>
      </v-form>
    </v-card>
  </v-dialog>
</template>

<script>

import DatePicker from '~/components/forms/DatePicker'
import StateSelector from '~/components/forms/State'


export default {
  name: "ManageVehicle",
  props: ['value', 'vehicle', 'fleet'],
  components: {
    DatePicker,
    StateSelector,
  },
  data: () => ({
    valid: true,
    showDates: true,
    working: false,
    formValid: false,
    fleets: [],
    vehicle_types: [],
    engine_manufacturers: [],
    errors: [],
    form: {
      vehicle_number: null,
      fleet_id: null,
    }
  }),
  async created () {
    let tFleets = await this.$axios.$get(`/fleets?paginate=false`)
    if (tFleets && tFleets.data) {
      this.fleets = tFleets.data
    }
    let tVehicleTypes = await this.$axios.$get(`/vehicle_types?paginate=false`)
    if (tVehicleTypes && tVehicleTypes.data) {
      this.vehicle_types = tVehicleTypes.data
    }
    let tEngineManufacturers = await this.$axios.$get(`/engine_manufacturers?paginate=false`)
    if (tEngineManufacturers && tEngineManufacturers.data) {
      this.engine_manufacturers = tEngineManufacturers.data
    }

    //if editing existing vehicle
    if (this.vehicle && this.vehicle.id) {
      this.form.vehicle_number = this.vehicle.vehicle_number
      this.form.fleet_id = this.vehicle.fleet_id
      this.form.vehicle_type_id = this.vehicle.vehicle_type_id
      this.form.engine_manufacturer_id = this.vehicle.engine_manufacturer_id
      this.form.year = this.vehicle.year
      this.form.make = this.vehicle.make
      this.form.model = this.vehicle.model
      this.form.vin = this.vehicle.vin
      this.form.tire_size = this.vehicle.tire_size
      this.form.purchase_price = this.vehicle.purchase_price
      this.form.in_service_date = this.vehicle.in_service_date
      this.form.license_plate_number = this.vehicle.license_plate_number
      this.form.license_state = this.vehicle.license_state
      this.form.engine_serial_number = this.vehicle.engine_serial_number
    }

    //if creating a new vehicle from a fleet, default fleet assignment
    if (this.fleet && this.fleet.id) {
      this.form.fleet_id = this.fleet.id
    }
  },
  methods: {
    async saveVehicle () {
      this.working = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.working = false
        return
      }

      try {
        if (this.vehicle && this.vehicle.id) {
          await this.$axios.$put(`/vehicles/${this.vehicle.id}`, this.form)
        } else {
          await this.$axios.$post(`/vehicles`, this.form)
        }
        this.$emit('vehicle-saved')
        this.working = false
        this.$emit('input')
        this.reset()
      } catch (e) {
        console.log("my error", e.message)
        if (e.response && e.response.status == 422) {
          this.errors = e.response.parsedErrors
        }
        else {
          this.errors.push ({msg: e})
        }
        this.working = false
      }
    },
    refresh () {
      //TODO: Ask Michael why datepicker needs this?  SSSF worked without it
      this.showDates = false
      this.$nextTick().then(() => {
        // Add the dates components back in
        this.showDates = true
      });
    },
    reset () {
      this.form = {
        vehicle_number: null,
      }
      this.$refs.form.resetValidation()
    }
  },
}
</script>

<style scoped>
  .v-card .flex-item {
    margin-bottom: 5px;
  }

  .flex-item.tz-form-item {
    max-width: 80%;
  }
</style>
