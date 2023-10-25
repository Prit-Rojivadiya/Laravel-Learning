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
          {{ preventive_maintenance_template && preventive_maintenance_template.id ? 'Edit' : 'New' }} Preventive Maintenance Template
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn dark text :loading="working" @click="savePreventiveMaintenanceTemplate()">
            {{ preventive_maintenance_template && preventive_maintenance_template.id ? 'Save' : 'Create' }} Preventive Maintenance Template
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
                  <v-text-field
                    v-model="form.name"
                    :rules="[v => !!v || 'Please enter a unique name for this preventive maintenance template']"
                    label="Name"
                    required
                    dense
                  />
                </div>
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
                  <v-text-field
                    v-model="form.desc"
                    :rules="[v => !!v || 'Please enter preventive maintenance description']"
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
                  <v-text-field v-model="form.length_meters" label="Interval" dense/>
                </div>
                <div class="flex-item tz-form-item">
                  <v-text-field v-model="form.length_days" label="Days" dense/>
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
              </div>
            </v-flex>
          </v-layout>
        </v-container>
      </v-form>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: "ManagePreventiveMaintenanceTemplate",
  props: ['value', 'preventive_maintenance_template'],
  data: () => ({
    valid: true,
    working: false,
    formValid: false,
    repair_order_types: [],
    system_meter_types: [],
    system_p_m_due_types: [],
    boolean_types: [
      {key: 'Yes', value: 1},
      {key: 'No', value: 0},
    ],
    errors: [],
    form: {
      name: null,
      repair_order_type_id: null,
      system_meter_type_id: null,
      system_p_m_due_type_id: null,
      recurring: true,
      length_meters: null,
      length_days: null,
      desc: null,
    }
  }),
  async created () {
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

    if (this.preventive_maintenance_template && this.preventive_maintenance_template.id) {
      this.form.name = this.preventive_maintenance_template.name
      this.form.repair_order_type_id = this.preventive_maintenance_template.repair_order_type_id
      this.form.system_meter_type_id = this.preventive_maintenance_template.system_meter_type_id
      this.form.system_p_m_due_type_id = this.preventive_maintenance_template.system_p_m_due_type_id
      this.form.recurring = this.preventive_maintenance_template.recurring
      this.form.length_meters = this.preventive_maintenance_template.length_meters
      this.form.length_days = this.preventive_maintenance_template.length_days
      this.form.desc = this.preventive_maintenance_template.desc
    }
  },
  methods: {
    async savePreventiveMaintenanceTemplate () {
      this.working = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.working = false
        return
      }

      try {
        if (this.preventive_maintenance_template && this.preventive_maintenance_template.id) {
          await this.$axios.$put(`/preventive_maintenance_templates/${this.preventive_maintenance_template.id}`, this.form)
        } else {
          await this.$axios.$post(`/preventive_maintenance_templates`, this.form)
        }
        this.$emit('preventive_maintenance_template-saved')
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
    reset () {
      this.form = {
        name: null,
        desc: null,
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
