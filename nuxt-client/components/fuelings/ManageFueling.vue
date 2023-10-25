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
          {{ fueling && fueling.id ? 'Edit' : 'New' }} Fuel Event
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn dark text :loading="working" @click="saveFueling()">
            {{ fueling && fueling.id ? 'Save' : 'Create' }} Fuel Event
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
        <v-card>
          <v-card-title>
            <div class="flex-item tz-form-item" v-if="allowVehicleAssignment">
              <v-select
                v-model="form.vehicle_id"
                :rules="[v => !!v || 'Please select the vehicle this fuel event belongs to']"
                :items="vehicles"
                item-value="id"
                item-text="vehicle_number"
                label="Vehicle"
                required
                dense
              />
            </div>
            <div class="flex-item" v-else>
              <span class="grey--text">Vehicle Unit Number:</span>
              <span>{{ vehicle.vehicle_number }}</span>
            </div>
          </v-card-title>
          <v-card-text>
            <v-container grid-list-sm justify-space-around fluid class="p-4">
              <v-layout row>
                <v-flex>
                  <div class="flex-content">
                    <div class="flex-item tz-form-item">
                      <DatePicker
                        v-if="showDates"
                        v-model="form.fueling_date"
                        label="Fueling Date"
                        @input="refresh"
                        required
                        :rules="[v => !!v || 'Please enter the date of the fuel event']"
                      />
                    </div>
                    <div class="flex-item tz-form-item">
                      <v-text-field
                        v-model="form.meter_reading"
                        :rules="[v => !!v || 'Please enter the meter reading at time of the fuel event']"
                        label="Meter Reading"
                        required
                        dense
                      />
                    </div>
                    <div class="flex-item tz-form-item">
                      <v-select
                        v-model="form.fuel_unit_type_id"
                        :rules="[v => !!v || 'Please select the fuel unit type for this fuel event']"
                        :items="fuelUnitTypes"
                        item-value="id"
                        item-text="name"
                        label="Fuel Unit Type"
                        required
                        dense
                      />
                    </div>
                    <div class="flex-item tz-form-item">
                      <v-text-field
                        v-model="form.price_per_unit"
                        label="Price Per Unit"
                        dense
                        required
                        :rules="[v => !!v || 'Please enter the Price Per Unit']"
                      />
                    </div>
                    <div class="flex-item tz-form-item">
                      <v-text-field
                        v-model="form.total_units"
                        label="Total Units"
                        dense
                        required
                        :rules="[v => !!v || 'Please enter the total units for this fuel event']"
                      />
                    </div>
                    <div class="flex-item tz-form-item">
                      <v-text-field v-model="form.total_price" label="Total Price" dense/>
                    </div>
                  </div>
                </v-flex>
                <v-flex>
                  <div class="flex-content">
                    <div class="flex-item tz-form-item" style="margin-top: 23px;">
                      <v-select
                        v-model="form.fuel_type_id"
                        :items="fuelTypes"
                        item-value="id"
                        item-text="name"
                        label="Fuel Type"
                        dense
                      />
                    </div>
                    <div class="flex-item tz-form-item">
                      <v-select
                        v-model="form.vendor_id"
                        :items="vendors"
                        item-value="id"
                        item-text="name"
                        label="Vendor"
                        dense
                      />
                    </div>
                    <div class="flex-item tz-form-item">
                      <v-select
                        v-model="form.location_country"
                        :items="countries"
                        item-value="id"
                        item-text="name"
                        label="Fueling Location Country"
                        dense
                      />
                    </div>
                    <div class="flex-item tz-form-item">
                      <StateSelector
                        v-model="form.location_state"
                        label="Fueling Location State"
                      />
                    </div>
                    <div class="flex-item tz-form-item">
                      <div class="flex-item tz-form-item">
                        <v-text-field
                          v-model="form.notes"
                          label="Notes"
                          dense
                        />
                      </div>
                    </div>
                  </div>
                </v-flex>
              </v-layout>
            </v-container>
          </v-card-text>
        </v-card>
      </v-form>
    </v-card>
  </v-dialog>
</template>

<script>

import DatePicker from '~/components/forms/DatePicker'
import StateSelector from '~/components/forms/State'

export default {
  name: "ManageFueling",
  props: ['value', 'fueling', 'vehicle', 'allowVehicleAssignment'],
  components: {
    DatePicker,
    StateSelector,
  },
  data: () => ({
    valid: true,
    showDates: true,
    working: false,
    formValid: false,
    vehicles: [],
    vendors: [],
    fuelTypes: [],
    fuelUnitTypes: [],
    countries: [],
    errors: [],
    form: {
      vehicle_id: null,
      fueling_date: null,
    }
  }),
  async created () {
    this.vehicles = [this.vehicle]

    //if editing existing fueling
    if (this.fueling && this.fueling.id) {
      this.form.vehicle_id = this.fueling.vehicle_id
      this.form.vendor_id = this.fueling.vendor_id
      this.form.fuel_unit_type_id = this.fueling.fuel_unit_type_id
      this.form.price_per_unit = this.fueling.price_per_unit
      this.form.total_units = this.fueling.total_units
      this.form.total_price = this.fueling.total_price
      this.form.meter_reading = this.fueling.meter_reading
      this.form.meter_reading_id = this.fueling.meter_reading_id
      this.form.fueling_date = this.fueling.fueling_date
      this.form.fuel_type_id = this.fueling.fuel_type_id
      this.form.location_country = this.fueling.location_country
      this.form.location_state = this.fueling.location_state
      this.form.notes = this.fueling.notes
    }

    //if creating a new fuel event from a vehicle, default vehicle assignment
    if (this.vehicle && this.vehicle.id) {
      this.form.vehicle_id = this.vehicle.id
    }

    this.countries = [{name: 'USA', value: 'usa'},{name: 'Canada', value: 'canada'}]

    let tVendors = await this.$axios.$get(`/vendors?paginate=false`)
    if (tVendors && tVendors.data) {
      this.vendors = tVendors.data
    }
    let tfuelTypes = await this.$axios.$get(`/fuel_types?paginate=false`)
    if (tfuelTypes && tfuelTypes.data) {
      this.fuelTypes = tfuelTypes.data
    }
    let tfuelUnitTypes = await this.$axios.$get(`/fuel_unit_types?paginate=false`)
    if (tfuelUnitTypes && tfuelUnitTypes.data) {
      this.fuelUnitTypes = tfuelUnitTypes.data
    }


  },
  methods: {
    async saveFueling () {
      this.working = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.working = false
        return
      }

      try {
        if (this.fueling && this.fueling.id) {
          await this.$axios.$put(`/fuelings/${this.fueling.id}`, this.form)
        } else {
          await this.$axios.$post(`/fuelings`, this.form)
        }
        this.$emit('fueling-saved')
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
