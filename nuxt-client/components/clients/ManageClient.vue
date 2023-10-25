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
          {{ client && client.id ? 'Edit' : 'New' }} Account
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn dark text :loading="working" @click="saveClient()">
            {{ client && client.id ? 'Save' : 'Create' }} Account
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
        <div class="overline px-4 pt-4 pb-2">Account Details</div>
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
              :rules="[v => !!v || 'Please enter a unique abbreviation or code for this client']"
              label="Abbrv"
              required
              dense
            />
          </v-col>
        </v-row>
        <v-row dense class="px-4">
          <v-col v-if="showMoveClientToTenant">
            <v-btn small color="info" style="float: left" class="my-4 mr-3" @click="moveClienttoTenant">CANT UNDO MOVE CLIENT TO TENANT</v-btn>
          </v-col>
        </v-row>

      </v-form>

    </v-card>

  </v-dialog>
</template>

<script>

export default {
  name: 'ManageClient',
  props: ['value', 'client'],
  data: () => ({
    valid: true,
    working: false,
    formValid: false,
    errors: [],
    form: {
      name: null,
      abbrv: null
    },
    showMoveClientToTenant: false // default to false
  }),
  async created () {
    if (this.client && this.client.id) {
      this.form.name = this.client.name
      this.form.abbrv = this.client.abbrv
    }
  },
  methods: {
    async saveClient () {
      this.working = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.working = false
        return
      }

      try {
        if (this.client && this.client.id) {
          await this.$axios.$put(`/clients/${this.client.id}`, this.form)
        } else {
          await this.$axios.$post(`/clients`, this.form)
        }
        this.$emit('client-saved')
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
        abbrv: null
      }
      this.$refs.form.resetValidation()
    },
    moveClienttoTenant () {
      this.$axios.$put(`/clients/movetotenant/${this.client.id}/${this.client.id}`)
    }
  }
}
</script>

<style scoped>

</style>
