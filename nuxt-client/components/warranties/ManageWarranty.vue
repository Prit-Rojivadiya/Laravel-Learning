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
          {{ warranty && warranty.id ? 'Edit' : 'New' }} Warranty
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn dark text :loading="working" @click="saveWarranty()">
            {{ warranty && warranty.id ? 'Save' : 'Create' }} Warranty
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
                :rules="[v => !!v || 'Please select the vehicle this warranty belongs to']"
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
                        v-model="form.name"
                        :rules="[v => !!v || 'Please enter name']"
                        label="Name"
                        required
                        dense
                      />
                    </div>
                    <div class="flex-item tz-form-item">
                      <v-text-field
                        v-model="form.desc"
                        :rules="[v => !!v || 'Please enter description of this warranty']"
                        label="Description"
                        required
                        dense
                      />
                    </div>
                  </div>
                </v-flex>
                <v-flex>
                  <div class="flex-content">
                    <div class="flex-item tz-form-item">
                      <v-text-field v-model="form.mileage_total" label="Warranty Total Mileage" dense/>
                    </div>
                    <div class="flex-item tz-form-item">
                      <v-text-field v-model="form.ending_mileage" label="Vehicle Mileage Ends" dense/>
                    </div>
                    <div class="flex-item tz-form-item">
                      <DatePicker
                        v-if="showDates"
                        v-model="form.ending_date"
                        label="Date Warranty Ends"
                        @input="refresh"
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

export default {
  name: "ManageWarranty",
  props: ['value', 'warranty', 'vehicle', 'allowVehicleAssignment'],
  components: {
    DatePicker,
  },
  data: () => ({
    valid: true,
    showDates: true,
    working: false,
    formValid: false,
    vehicles: [],
    errors: [],
    form: {
      vehicle_id: null,
    }
  }),
  async created () {
    this.vehicles = [this.vehicle]

    //if editing existing warranty
    if (this.warranty && this.warranty.id) {
      this.form.vehicle_id = this.warranty.vehicle_id
      this.form.name = this.warranty.name
      this.form.desc = this.warranty.desc
      this.form.ending_date = this.warranty.ending_date
      this.form.ending_mileage = this.warranty.ending_mileage
      this.form.mileage_total = this.warranty.mileage_total
    }

    //if creating a new warranty from a vehicle, default vehicle assignment
    if (this.vehicle && this.vehicle.id) {
      this.form.vehicle_id = this.vehicle.id
    }

  },
  methods: {
    async saveWarranty () {
      this.working = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.working = false
        return
      }

      try {
        if (this.warranty && this.warranty.id) {
          await this.$axios.$put(`/warranties/${this.warranty.id}`, this.form)
        } else {
          await this.$axios.$post(`/warranties`, this.form)
        }
        this.$emit('warranty-saved')
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
        name: null,
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
