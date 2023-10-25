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
          {{ vendor && vendor.id ? 'Edit' : 'New' }} Vendor
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn dark text :loading="working" @click="saveVendor()">
            {{ vendor && vendor.id ? 'Save' : 'Create' }} Vendor
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
              v-model="form.abbrv"
              :rules="[v => !!v || 'Please enter a unique abbreviation or code for this vendor']"
              label="Abbrv"
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
  name: "ManageVendor",
  props: ['value', 'vendor'],
  data: () => ({
    valid: true,
    working: false,
    formValid: false,
    errors: [],
    form: {
      name: null,
      abbrv: null,
    }
  }),
  async created () {
    if (this.vendor && this.vendor.id) {
      this.form.name = this.vendor.name
      this.form.abbrv = this.vendor.abbrv
    }
  },
  methods: {
    async saveVendor () {
      this.working = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.working = false
        return
      }

      try {
        if (this.vendor && this.vendor.id) {
          await this.$axios.$put(`/vendors/${this.vendor.id}`, this.form)
        } else {
          await this.$axios.$post(`/vendors`, this.form)
        }
        this.$emit('vendor-saved')
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
        abbrv: null,
      }
      this.$refs.form.resetValidation()
    }
  },
}
</script>

<style scoped>

</style>
