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
          {{ fleet && fleet.id ? 'Edit' : 'New' }} Fleet
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn dark text :loading="working" @click="saveFleet()">
            {{ fleet && fleet.id ? 'Save' : 'Create' }} Fleet
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
            <v-text-field
              v-model="form.name"
              :rules="[v => !!v || 'Please enter name']"
              label="Name"
              required
              dense
            />
          </v-col>
        </v-row>
        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <v-text-field
              v-model="form.fleet_number"
              :rules="[v => !!v || 'Please enter a unique fleet number or abbreviation for this fleet']"
              label="Fleet Number"
              required
              dense
            />
          </v-col>
        </v-row>
        <v-row dense class="px-4">
          <v-col cols="6" dense>
            <v-select
              v-model="form.branch_id"
              :rules="[v => !!v || 'Please select the branch this fleet belongs to']"
              :items="branches"
              item-value="id"
              item-text="name"
              label="Branch"
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
  name: "ManageFleet",
  props: ['value', 'fleet', 'branch'],
  data: () => ({
    valid: true,
    working: false,
    formValid: false,
    branches: [],
    errors: [],
    form: {
      name: null,
      branch_id: null,
    }
  }),
  async created () {
    if (this.fleet && this.fleet.id) {
      this.form.name = this.fleet.name
      this.form.fleet_number = this.fleet.fleet_number
      this.form.branch_id = this.fleet.branch_id
    }
    if (this.branch && this.branch.id) {
      this.form.branch_id = this.branch.id
    }
    let tBranches = await this.$axios.$get(`/branches?paginate=false`)
    if (tBranches && tBranches.data) {
      this.branches = tBranches.data
    }
  },
  methods: {
    async saveFleet () {
      this.working = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.working = false
        return
      }

      try {
        if (this.fleet && this.fleet.id) {
          await this.$axios.$put(`/fleets/${this.fleet.id}`, this.form)
        } else {
          await this.$axios.$post(`/fleets`, this.form)
        }
        this.$emit('fleet-saved')
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
      }
      this.$refs.form.resetValidation()
    }
  },
}
</script>

<style scoped>

</style>
