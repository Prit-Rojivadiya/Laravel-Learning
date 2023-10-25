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
          {{ repair_order && repair_order.id ? 'Edit' : 'New' }} Repair Order
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn dark text :loading="working" @click="saveRepairOrder()">
            {{ repair_order && repair_order.id ? 'Save' : 'Create' }} Repair Order
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
                :rules="[v => !!v || 'Please select the vehicle this repair order belongs to']"
                :items="vehicles"
                item-value="id"
                item-text="vehicle_number"
                label="Vehicle"
                required
                dense
              />
            </div>
            <div class="flex-item" v-else-if="vehicle">
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
                      <v-text-field
                        v-model="form.desc"
                        :rules="[v => !!v || 'Please enter a description of the repair description']"
                        label="Repair Description"
                        required
                        dense
                      />
                    </div>
                  </div>
                </v-flex>
              </v-layout>
              <v-layout row>
                <v-flex>
                  <div class="flex-item tz-form-item">
                    <v-select
                      v-model="form.vendor_id"
                      :rules="[v => !!v || 'Please select the vendor for this repair order']"
                      :items="vendors"
                      item-value="id"
                      item-text="name"
                      label="Vendor"
                      required
                      dense
                    />
                  </div>
                  <div class="flex-item tz-form-item">
                    <v-select
                      v-model="form.repair_order_status_id"
                      :rules="[v => !!v || 'Please select the repair order status']"
                      :items="repair_order_statuses"
                      item-value="id"
                      item-text="name"
                      label="Status"
                      required
                      dense
                    />
                  </div>
                  <div class="flex-content">
                    <div class="flex-item tz-form-item">
                      <v-select
                        v-model="form.needs_approval"
                        :items="boolean_types"
                        item-value="value"
                        item-text="key"
                        label="Needs Approval"
                        required
                        dense
                      />
                    </div>
                    <div class="flex-item tz-form-item">
                      <DatePicker
                        v-if="showDates"
                        v-model="form.approval_received_date"
                        label="Approval Received"
                        @input="refresh('approval_received_date')"
                        required
                      />
                    </div>
<!--                    <div class="flex-item tz-form-item">-->
<!--                      <v-select-->
<!--                        v-model="form.preventive_maintenance_id"-->
<!--                        :items="vehicle_pms"-->
<!--                        item-value="id"-->
<!--                        item-text="name"-->
<!--                        label="Link to Preventive Maintenance Item"-->
<!--                        dense-->
<!--                      />-->
<!--                    </div>-->
                  </div>
                </v-flex>
                <v-flex>
                  <div class="flex-item tz-form-item">
                    <DatePicker
                      v-if="showDates"
                      v-model="form.start_date"
                      label="Start Date"
                      @input="refresh('start_date')"
                      required
                    />
                  </div>
                  <div class="flex-item tz-form-item">
                    <DatePicker
                      v-if="showDates"
                      v-model="form.completed_date"
                      label="Completed Date"
                      @input="refresh('completed_date')"
                      required
                    />
                  </div>
                  <div class="flex-item tz-form-item">
                    <v-text-field v-model.number="form.meter_reading" label="Meter Reading" dense/>
                  </div>
                  <div class="flex-item tz-form-item">
                    <v-text-field
                      v-model="form.invoice_number"
                      label="Invoice Number"
                      dense
                    />
                  </div>
                </v-flex>
              </v-layout>
              <v-layout row>
                <v-flex>
                  <div class="flex-content">
                    <div class="flex-item tz-form-item">
                      <v-text-field
                        v-model="form.notes"
                        label="Additional Notes"
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

export default {
  name: "ManageRepairOrder",
  props: ['value', 'repair_order', 'vehicle', 'allowVehicleAssignment', 'linkToPM'],
  components: {
    DatePicker,
  },
  data: () => ({
    valid: true,
    working: false,
    formValid: false,
    showDates: true,
    vendors: [],
    repair_order_statuses: [],
    //vehicle_pms: [],
    boolean_types: [
      {key: 'Yes', value: 1},
      {key: 'No', value: 0},
    ],
    vehicles: [],
    errors: [],
    form: {
      vehicle_id: null,
      vendor_id: null,
      repair_order_status_id: null,
      needs_approval: false,
      approval_received_date: null,
      start_date: null,
      completed_date: null,
      meter_reading: null,
      invoice_number: null,
      desc: null,
      notes: null,
      linkToPMId: null
    },
  }),
  async created () {
    //if creating a new repair order  from a vehicle, default vehicle assignment
    if (this.vehicle && this.vehicle.id) {
      this.form.vehicle_id = this.vehicle.id
      this.vehicles = [this.vehicle]
    }

    if (this.allowVehicleAssignment) {
      let tVehicles = await this.$axios.$get(`/vehicles?paginate=false`)
      if (tVehicles && tVehicles.data) {
        this.vehicles = tVehicles.data
      }
    }

    if (this.linkToPM) {
      this.form.linkToPMId = this.linkToPM.id
    }

    let tVendors = await this.$axios.$get(`/vendors?paginate=false`)
    if (tVendors && tVendors.data) {
      this.vendors = tVendors.data
    }
    let tRepairOrderStatuses = await this.$axios.$get(`/repair_order_statuses?paginate=false`)
    if (tRepairOrderStatuses && tRepairOrderStatuses.data) {
      this.repair_order_statuses = tRepairOrderStatuses.data
    }
    //let tVehicle_pms = await this.$axios.$get(`/preventive_maintenances?paginate=false&filterByVehicle=xxxx&filterByStatus=xxxx`)
    // let tVehicle_pms = await this.$axios.$get(
    //   'preventive_maintenances', {
    //     params: {
    //       paginate: false,
    //       filterByVehicle: this.form.vehicle_id,
    //       filterByStatus: "un-assigned",
    //     }
    //   }
    // )
    // if (tVehicle_pms && tVehicle_pms.data) {
    //   this.vehicle_pms = tVehicle_pms.data
    // }

    if (this.repair_order && this.repair_order.id) {
      this.form.vehicle_id = this.repair_order.vehicle_id
      this.form.vendor_id = this.repair_order.vendor_id
      this.form.repair_order_status_id = this.repair_order.repair_order_status_id
      this.form.needs_approval = this.repair_order.needs_approval
      this.form.approval_received_date = this.repair_order.approval_received_date
      this.form.start_date = this.repair_order.start_date
      this.form.completed_date = this.repair_order.completed_date
      this.form.meter_reading = this.repair_order.meter_reading
      this.form.invoice_number = this.repair_order.invoice_number
      this.form.desc = this.repair_order.desc
      this.form.notes = this.repair_order.notes

      if (this.repair_order.start_date != null) {
        let tStartDate = new Date(this.repair_order.start_date)
        this.form.start_date = (new Date(tStartDate.getTime())).toISOString().split('T')[0]
      }
      if (this.repair_order.completed_date != null) {
        let tCompletedDate = new Date(this.repair_order.completed_date)
        this.form.completed_date = (new Date(tCompletedDate.getTime())).toISOString().split('T')[0]
      }
    }
    else {
      let tStartDate = new Date()
      this.form.start_date = (new Date(tStartDate.getTime())).toISOString().split('T')[0]
      for (let i in this.repair_order_statuses) {
        if (this.repair_order_statuses[i].name.toLowerCase().indexOf('new') > -1) {
          this.form.repair_order_status_id = this.repair_order_statuses[i].id
          break
        }
      }

    }
  },
  watch: {
    'form.repair_order_status_id' (newVal, oldVal) {
      if (oldVal != null && newVal != null) {
        for (let i in this.repair_order_statuses) {
          if (this.repair_order_statuses[i].id == newVal && this.repair_order_statuses[i].name.toLowerCase().indexOf('closed') > -1) {
            let tCompletedDate = new Date()
            this.form.completed_date = (new Date(tCompletedDate.getTime())).toISOString().split('T')[0]
            break
          }
        }
      }
    },
    'form.completed_date' (newVal, oldVal) {
      if (newVal != null) {
        for (let i in this.repair_order_statuses) {
          if (this.repair_order_statuses[i].name.toLowerCase().indexOf('closed') > -1) {
            this.form.repair_order_status_id = this.repair_order_statuses[i].id
            break
          }
        }
      }
    },
  },
  methods: {
    async saveRepairOrder () {
      this.working = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.working = false
        return
      }

      try {
        if (this.repair_order && this.repair_order.id) {
          await this.$axios.$put(`/repair_orders/${this.repair_order.id}`, this.form)
        } else {
          await this.$axios.$post(`/repair_orders`, this.form)
        }
        this.$emit('repair_order-saved')
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
    refresh (field) {
      //doing watch here, instead of in watch() as it doesn't trigger change on vue component
      //TODO: Ask Michael why datepicker needs this?  SSSF worked without it
      this.showDates = false
      this.$nextTick().then(() => {
        // Add the dates components back in
        this.showDates = true
      });
    },
    reset () {
      this.form = {
        desc: null,
      }
      this.$refs.form.resetValidation()
    },
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
