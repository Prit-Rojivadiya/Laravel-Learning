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
          {{ vehicle_type && vehicle_type.id ? 'Edit' : 'New' }} Vehicle Type
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn dark text :loading="working" @click="saveVehicleType()">
            {{ vehicle_type && vehicle_type.id ? 'Save' : 'Create' }} Vehicle Type
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
        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <v-select
              v-model="form.system_vehicle_type_id"
              :rules="[v => !!v || 'Please select the vehicle type']"
              :items="system_vehicle_types"
              item-value="id"
              item-text="name"
              label="Type"
              required
              dense
            />
          </v-col>
        </v-row>
        <v-row dense class="px-4">
            <v-col lg="6" sm="6" xs="12" dense>
              <v-text-field
                v-model="form.name"
                :rules="[v => !!v || 'Please enter a unique name for this vehicle type']"
                label="Name"
                required
                dense
              />
            </v-col>
        </v-row>
        <v-row dense class="px-4">
            <v-col lg="6" sm="6" xs="12" dense>
              <v-text-field
                v-model="form.desc"
                :rules="[v => !!v || 'Please enter vehicle type description']"
                label="Description"
                required
                dense
              />
            </v-col>
        </v-row>

      </v-form>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: "ManageVehicleType",
  props: ['value', 'vehicle_type'],
  data: () => ({
    valid: true,
    working: false,
    formValid: false,
    system_vehicle_types: [],
    errors: [],
    form: {
      name: null,
      system_vehicle_type_id: null,
      desc: null,
    }
  }),
  async created () {
    let tSysVehicleTypes = await this.$axios.$get(`/system_vehicle_types?paginate=false`)
    if (tSysVehicleTypes && tSysVehicleTypes.data) {
      this.system_vehicle_types = tSysVehicleTypes.data
    }

    if (this.vehicle_type && this.vehicle_type.id) {
      this.form.name = this.vehicle_type.name
      this.form.system_vehicle_type_id = this.vehicle_type.system_vehicle_type_id
      this.form.desc = this.vehicle_type.desc
    }
  },
  methods: {
    async saveVehicleType () {
      this.working = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.working = false
        return
      }

      try {
        if (this.vehicle_type && this.vehicle_type.id) {
          await this.$axios.$put(`/vehicle_types/${this.vehicle_type.id}`, this.form)
        } else {
          await this.$axios.$post(`/vehicle_types`, this.form)
        }
        this.$emit('vehicle_type-saved')
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

</style>
