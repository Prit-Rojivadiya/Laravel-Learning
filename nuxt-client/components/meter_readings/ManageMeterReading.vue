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
          {{ meterReading && meterReading.id ? 'Edit' : 'New' }} Meter Reading
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn dark text :loading="working" @click="saveMeterReading()">
            {{ meterReading && meterReading.id ? 'Save' : 'Create' }} Meter Reading
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
                :rules="[v => !!v || 'Please select the vehicle this meter reading belongs to']"
                :items="vehicles"
                item-value="id"
                item-text="vehicle_number"
                label="Vehicle"
                required
                dense
              />
            </div>
            <div class="flex-item" v-else>
              <span class="grey--text">Vehicle:</span>
              <span>{{ vehicle.vehicle_number }}</span>
            </div>
          </v-card-title>
          <v-card-text>
            <v-container grid-list-sm justify-space-around fluid class="p-4">
              <v-layout row>
                <v-flex>
                  <div class="flex-content">
                    <div class="flex-item tz-form-item">
                      <v-text-field
                        v-model="form.meter_reading"
                        :rules="[v => !!v || 'Please enter the meter reading value']"
                        label="Meter Reading Value"
                        required
                        dense
                      />
                    </div>
                    <div class="flex-item tz-form-item">
                      <DatePicker
                        v-if="showDates"
                        v-model="form.meter_reading_date"
                        label="Date (mm/dd/yyyy)"
                        @input="refresh"
                      />
                    </div>
                    <div class="flex-item tz-form-item">
                      <v-select
                        v-model="form.source"
                        :rules="[v => !!v || 'Please select the source of this meter reading']"
                        :items="sources"
                        item-value="name"
                        item-text="name"
                        label="Source"
                        required
                        dense
                      />
                    </div>
                  </div>
                </v-flex>
                <v-flex>
                  <div class="flex-content">
                    <div class="flex-item tz-form-item">
                      <v-select
                        v-model="form.location_country"
                        :items="countries"
                        item-value="id"
                        item-text="name"
                        label="Country"
                        dense
                      />
                    </div>
                    <div class="flex-item tz-form-item">
                      <StateSelector
                        v-model="form.location_state"
                        label="State"
                      />
                    </div>
                    <div class="flex-item tz-form-item">
                      <v-text-field
                        v-model="form.notes"
                        label="Notes"
                        dense
                      />
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
  name: 'ManageMeterReading',
  props: ['value', 'meterReading', 'vehicle', 'allowVehicleAssignment'],
  components: {
    DatePicker,
    StateSelector
  },
  data: () => ({
    valid: true,
    showDates: true,
    working: false,
    formValid: false,
    vehicles: [],
    errors: [],
    countries: [],
    form: {
      vehicle_id: null,
      meter_reading_date: null,
    }
  }),
  async created () {
    this.vehicles = [this.vehicle]
    this.sources = [{ name: 'Fueling' }, { name: 'Repair Order' }, { name: 'Enter State Line' }, { name: 'Exit State Line' }, { name: 'Inspection' }]
    this.countries = [{ name: 'USA', value: 'usa' }, { name: 'Canada', value: 'canada' }]

    // if editing existing meterReading
    if (this.meterReading && this.meterReading.id) {
      this.form.vehicle_id = this.meterReading.vehicle_id
      this.form.meter_reading = this.meterReading.meter_reading
      this.form.meter_reading_date = this.meterReading.meter_reading_date
      this.form.notes = this.meterReading.notes
      this.form.source_id = this.meterReading.source_id
      this.form.source = this.meterReading.source
      this.form.location_state = this.meterReading.location_state
      this.form.location_country = this.meterReading.location_country
    }

    // if creating a new meter reading from a vehicle, default vehicle assignment
    if (this.vehicle && this.vehicle.id) {
      this.form.vehicle_id = this.vehicle.id
    }

  },
  methods: {
    async saveMeterReading () {
      this.working = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.working = false
        return
      }

      try {
        if (this.meterReading && this.meterReading.id) {
          await this.$axios.$put(`/meter_readings/${this.meterReading.id}`, this.form)
        } else {
          await this.$axios.$post(`/meter_readings`, this.form)
        }
        this.$emit('meterReading-saved')
        this.working = false
        this.$emit('input')
        this.reset()
      } catch (e) {
        console.log('my error', e.message)
        if (e.response && e.response.status === 422) {
          this.errors = e.response.parsedErrors
        }
        else {
          this.errors.push({ msg: e })
        }
        this.working = false
      }
    },
    refresh () {
      // TODO: Ask Michael why datepicker needs this?  SSSF worked without it
      this.showDates = false
      this.$nextTick().then(() => {
        // Add the dates components back in
        this.showDates = true
      })
    },
    reset () {
      this.form = {
        name: null
      }
      this.$refs.form.resetValidation()
    }
  }
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
