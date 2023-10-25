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
          {{ engine_manufacturer && engine_manufacturer.id ? 'Edit' : 'New' }} Engine Manufacturer
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn dark text :loading="working" @click="saveEngineManufacturer()">
            {{ engine_manufacturer && engine_manufacturer.id ? 'Save' : 'Create' }} Engine Manufacturer
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
          <v-row dense class="px-4">
            <v-col lg="6" sm="6" xs="12" dense>
              <v-text-field
                v-model="form.name"
                :rules="[v => !!v || 'Please enter a unique name for this Engine Manufacturer']"
                label="Name"
                required
                dense
              />
            </v-col>
          </v-row>
        </v-row>

      </v-form>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: "ManageEngineManufacturer",
  props: ['value', 'engine_manufacturer'],
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
    if (this.engine_manufacturer && this.engine_manufacturer.id) {
      this.form.name = this.engine_manufacturer.name
    }
  },
  methods: {
    async saveEngineManufacturer () {
      this.working = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.working = false
        return
      }

      try {
        if (this.engine_manufacturer && this.engine_manufacturer.id) {
          await this.$axios.$put(`/engine_manufacturers/${this.engine_manufacturer.id}`, this.form)
        } else {
          await this.$axios.$post(`/engine_manufacturers`, this.form)
        }
        this.$emit('engine_manufacturer-saved')
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
