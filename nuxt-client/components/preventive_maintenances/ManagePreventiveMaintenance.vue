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
          {{ preventive_maintenance && preventive_maintenance.id ? 'Edit' : 'New' }} Preventive Maintenance Item
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn dark text :loading="working" @click="savePreventiveMaintenance()">
            {{ preventive_maintenance && preventive_maintenance.id ? 'Save' : 'Create' }} Preventive Maintenance Item
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
                :rules="[v => !!v || 'Please select the vehicle this PM item belongs to']"
                :items="vehicles"
                item-value="id"
                item-text="vehicle_number"
                label="Vehicle"
                required
                dense
              />
            </div>
            <div class="flex-item" v-else-if="vehicle">
              <span class="grey--text">Vehicle:</span>
              <span>{{ vehicle.vehicle_number }}</span>
            </div>
          </v-card-title>
          <v-card-text>
            <v-container grid-list-sm justify-space-around fluid class="p-4">
              <v-layout row>
                <v-flex>
                  <div class="flex-item tz-form-item">
                    <v-select
                      v-model="form.preventive_maintenance_template_id"
                      :rules="[v => !!v || 'Please select the associated PM Template to use for this PM instance']"
                      :items="pmTemplates"
                      item-value="id"
                      item-text="name"
                      label="PM Template"
                      @change="updatePMTemplateFields"
                      required
                      dense
                    />
                  </div>
                  <div class="flex-content">
                    <div class="flex-item tz-form-item">
                      <v-select
                        v-model="form.recurring"
                        :items="boolean_types"
                        item-value="value"
                        item-text="key"
                        label="Recurring"
                        required
                        dense
                      />
                    </div>
                    <div class="flex-item tz-form-item">
                      <v-select
                        v-model="form.repair_order_type_id"
                        :rules="[v => !!v || 'Please select the repair order type']"
                        :items="repair_order_types"
                        item-value="id"
                        item-text="name"
                        label="Type"
                        required
                        dense
                      />
                    </div>
                    <div class="flex-item tz-form-item">
                      <v-select
                        v-model="form.system_meter_type_id"
                        :rules="[v => !!v || 'Please select the meter type']"
                        :items="system_meter_types"
                        item-value="id"
                        item-text="name"
                        label="Meter Type"
                        required
                        dense
                      />
                    </div>
                    <div class="flex-item tz-form-item">
                      <v-text-field v-model.number="form.length_meters" label="Interval" dense/>
                    </div>
                    <div class="flex-item tz-form-item">
                      <v-text-field v-model.number="form.length_days" label="Days" dense/>
                    </div>
                    <div class="flex-item tz-form-item">
                      <v-select
                        v-model="form.system_p_m_due_type_id"
                        :rules="[v => !!v || 'Please select the PM Schedule Due Type']"
                        :items="system_p_m_due_types"
                        item-value="id"
                        item-text="name"
                        label="PM Schedule Due Type"
                        required
                        dense
                      />
                    </div>
                    <div class="flex-item tz-form-item">
                      <v-autocomplete
                        v-model="form.repair_order_id"
                        :items="vehicle_ros"
                        item-value="id"
                        item-text="name"
                        label="Link to Repair Order"
                        dense
                        :filter="repairOrderSearchFilter"
                      >
                        <template slot="selection" slot-scope="data">
                          <!-- HTML that describe how select should render selected items -->
                          {{ getROName(data.item) }}
                        </template>
                        <template slot="item" slot-scope="data">
                          <!-- HTML that describe how select should render items when the select is open -->
                          {{ data.item.ro_number }}: {{ data.item.desc }}
                        </template>
                      </v-autocomplete>
                    </div>
                  </div>
                </v-flex>
                <v-flex>
                  <div class="flex-content">
                    <div class="flex-item tz-form-item">
                      <v-text-field
                        v-model="form.name"
                        :rules="[v => !!v || 'Please enter a name for this preventive maintenance item']"
                        label="PM Name"
                        required
                        dense
                      />
                    </div>
                  </div>
                  <div class="flex-item tz-form-item">
                    <v-text-field v-model.number="form.start_at_meter" label="Start At Meter" required dense/>
                  </div>
                  <div class="flex-item tz-form-item">
                    <v-text-field v-model.number="form.due_at_meter" label="Due At Meter" dense/>
                  </div>
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
                      v-model="form.due_date"
                      label="Due Date"
                      @input="refresh"
                    />
                  </div>
                  <div class="flex-item tz-form-item">
                    <v-text-field v-model="form.completed_at_meter" label="Completed At Meter" dense/>
                  </div>
                  <div class="flex-item tz-form-item">
                    <DatePicker
                      v-if="showDates"
                      v-model="form.completed_date"
                      label="Completed Date"
                      @input="refresh"
                    />
                  </div>
                </v-flex>
              </v-layout>
              <v-layout row>
                <v-flex>
                  <div class="flex-content">
                    <div class="flex-item tz-form-item">
                      <v-text-field
                        v-model="form.desc"
                        label="Description"
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
  name: "ManagePreventiveMaintenance",
  props: ['value', 'preventive_maintenance', 'pm_template', 'vehicle', 'allowVehicleAssignment'],
  components: {
    DatePicker,
  },
  data: () => ({
    valid: true,
    working: false,
    formValid: false,
    showDates: true,
    repair_order_types: [],
    system_meter_types: [],
    system_p_m_due_types: [],
    boolean_types: [
      {key: 'Yes', value: 1},
      {key: 'No', value: 0},
    ],
    vehicles: [],
    vehicle_ros: [],
    pmTemplates: [],
    pmTemplatesIndex: {},
    errors: [],
    form: {
      name: null,
      repair_order_type_id: null,
      system_meter_type_id: null,
      system_p_m_due_type_id: null,
      start_at: null,
      due_at: null,
      completed_at: null,
      start_at_meter: null,
      due_at_meter: null,
      completed_at_meter: null,
      recurring: true,
      length_meters: null,
      length_days: null,
      desc: null,
    }
  }),
  async created () {
    //if creating a new pm item from a vehicle, default vehicle assignment
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

    let tRepairOrderTypes = await this.$axios.$get(`/repair_order_types?paginate=false`)
    if (tRepairOrderTypes && tRepairOrderTypes.data) {
      this.repair_order_types = tRepairOrderTypes.data
    }
    let tSystemMeterTypes = await this.$axios.$get(`/system_meter_types?paginate=false`)
    if (tSystemMeterTypes && tSystemMeterTypes.data) {
      this.system_meter_types = tSystemMeterTypes.data
    }
    let tPMDueTypes = await this.$axios.$get(`/system_p_m_due_types?paginate=false`)
    if (tPMDueTypes && tPMDueTypes.data) {
      this.system_p_m_due_types = tPMDueTypes.data
    }

    let tPMTemplates = await this.$axios.$get(`/preventive_maintenance_templates?paginate=false`)
    if (tPMTemplates && tPMTemplates.data) {
      this.pmTemplates = tPMTemplates.data
      for (let pmT of this.pmTemplates) {
        this.pmTemplatesIndex[pmT.id] = pmT
      }
    }

    let tVehicle_ros = await this.$axios.$get(
      'repair_orders', {
        params: {
          filterByVehicle: this.form.vehicle_id,
          paginate: true,
          _sort: "created_at",
          _sort_dir: "desc"
        }
      }
    )
    if (tVehicle_ros) {
      this.vehicle_ros = tVehicle_ros.data
    }
    // if (this.form.repair_order_id) {
    //   let tVehicle_ro = await this.$axios.$get(`/repair_order/${this.form.repair_order_id}`)
    //   this.vehicle_ros.push(tVehicle_ro)
    // }

    if (this.preventive_maintenance && this.preventive_maintenance.id) {
      this.form.name = this.preventive_maintenance.name
      this.form.preventive_maintenance_template_id = this.preventive_maintenance.preventive_maintenance_template_id
      this.form.repair_order_type_id = this.preventive_maintenance.repair_order_type_id
      this.form.system_meter_type_id = this.preventive_maintenance.system_meter_type_id
      this.form.system_p_m_due_type_id = this.preventive_maintenance.system_p_m_due_type_id
      this.form.recurring = this.preventive_maintenance.recurring
      this.form.start_at_meter = this.preventive_maintenance.start_at_meter
      this.form.due_at_meter = this.preventive_maintenance.due_at_meter
      this.form.completed_at_meter = this.preventive_maintenance.completed_at_meter
      this.form.length_meters = this.preventive_maintenance.length_meters
      this.form.length_days = this.preventive_maintenance.length_days
      this.form.desc = this.preventive_maintenance.desc
      this.form.repair_order_id = this.preventive_maintenance.repair_order_id

      if (this.preventive_maintenance.start_date) {
        let tStartDate = new Date(this.preventive_maintenance.start_date)
        this.form.start_date = (new Date(tStartDate.getTime())).toISOString().split('T')[0]
      }
      if (this.preventive_maintenance.due_date) {
        let tDueDate = new Date(this.preventive_maintenance.due_date)
        this.form.due_date = (new Date(tDueDate.getTime())).toISOString().split('T')[0]
      }
      if (this.preventive_maintenance.completed_date) {
        let tCompletedDate = new Date(this.preventive_maintenance.completed_date)
        this.form.completed_date = (new Date(tCompletedDate.getTime())).toISOString().split('T')[0]
      }
    }
  },
  watch: {
    'form.length_days' (newVal, oldVal) {
      if (oldVal != null) {
        let tStartDate = new Date(this.form.start_date)
        let tDueDate = new Date(tStartDate.valueOf())
        tDueDate.setDate(tDueDate.getDate() + newVal)
        this.form.due_date = (new Date(tDueDate.getTime())).toISOString().split('T')[0]
      }
    },
    'form.length_meters' (newVal, oldVal) {
      if (oldVal != null) {
        this.form.due_at_meter = this.form.start_at_meter + newVal
      }
    },
    'form.start_at_meter' (newVal, oldVal) {
      if (oldVal != null) {
        this.form.due_at_meter = newVal + this.form.length_meters
      }
    },
  },
  methods: {
    async savePreventiveMaintenance () {
      this.working = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.working = false
        return
      }

      try {
        if (this.preventive_maintenance && this.preventive_maintenance.id) {
          await this.$axios.$put(`/preventive_maintenances/${this.preventive_maintenance.id}`, this.form)
        } else {
          await this.$axios.$post(`/preventive_maintenances`, this.form)
        }
        this.$emit('preventive_maintenance-saved')
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
      if (field == 'start_date') {
        let tStartDate = new Date(this.form.start_date)
        let tDueDate = new Date(tStartDate.valueOf())
        tDueDate.setDate(tDueDate.getDate() + this.form.length_days)
        this.form.due_date = (new Date(tDueDate.getTime())).toISOString().split('T')[0]
      }
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
        desc: null,
      }
      this.$refs.form.resetValidation()
    },
    async updatePMTemplateFields() {
      let pmT = this.pmTemplatesIndex[this.form.preventive_maintenance_template_id]
      this.form.repair_order_type_id = pmT.repair_order_type_id
      this.form.system_meter_type_id = pmT.system_meter_type_id
      this.form.system_p_m_due_type_id = pmT.system_p_m_due_type_id
      this.form.recurring = pmT.recurring
      this.form.length_meters = pmT.length_meters
      this.form.length_days = pmT.length_days
      this.form.name = pmT.name

      let tStartDate = new Date()
      let tDueDate = new Date(tStartDate.valueOf())
      tDueDate.setDate(tDueDate.getDate() + pmT.length_days)
      //const offset = tStartDate.getTimezoneOffset()
      //this.form.start_date = (new Date(tStartDate.getTime() - (offset*60*1000))).toISOString().split('T')[0]
      //this.form.due_date = (new Date(tDueDate.getTime() - (offset*60*1000))).toISOString().split('T')[0]
      this.form.start_date = (new Date(tStartDate.getTime())).toISOString().split('T')[0]
      this.form.due_date = (new Date(tDueDate.getTime())).toISOString().split('T')[0]

      if (this.form.vehicle_id) {
        let tLatestVMR = await this.$axios.$get(`/vehicle_meter_reading/${this.form.vehicle_id}?latest=true`)
        let tLatestMeterReading = null
        if (tLatestVMR != null && Object.keys(tLatestVMR).length > 0) {
          tLatestMeterReading = tLatestVMR.meter_reading
        }
        else {
          tLatestMeterReading = 0
        }
        this.form.start_at_meter = tLatestMeterReading
        this.form.due_at_meter = tLatestMeterReading + pmT.length_meters
      }
    },
    repairOrderSearchFilter (item, queryText, itemText) {
      let itemName = item.ro_number.toLowerCase() + item.desc.toLowerCase()
      let searchText = queryText.toLowerCase()
      return itemName.indexOf(searchText) > -1
    },
    getROName(item) {
      if (item && item.desc.length > 20) {
        return `${item.ro_number}: ${item.desc.substring(0,20)}...`
      }
      else {
        return `${item.ro_number}: ${item.desc}`
      }
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
