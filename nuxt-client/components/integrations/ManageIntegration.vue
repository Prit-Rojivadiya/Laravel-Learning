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
          {{ integration && integration.id ? 'Edit' : 'New' }} Integration
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn dark text :loading="working" @click="saveIntegration()">
            {{ integration && integration.id ? 'Save' : 'Create' }} Integration
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
        <div class="overline px-4 pt-4 pb-2">Integration Details</div>
        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <v-checkbox
              v-model="form.active"
              label="Enabled"
              required
              dense
            />
          </v-col>
        </v-row>
        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <v-select
              v-model="form.client_id"
              :rules="[v => !!v || 'Please select the account for this integration']"
              :items="clients"
              item-value="id"
              item-text="name"
              label="Account"
              required
              dense
            />
          </v-col>
        </v-row>
        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <v-select
              v-model="form.name"
              :rules="[v => !!v || 'Please select the integration type']"
              :items="integrationTypes"
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
              v-model="form.username"
              label="Integration Username"
              required
              dense
            />
          </v-col>
        </v-row>
        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <v-text-field
              v-model="form.password"
              label="Integration Password"
              type="password"
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
  name: "ManageIntegration",
  props: ['value', 'integration', 'client'],
  data: () => ({
    valid: true,
    working: false,
    formValid: false,
    clients: [],
    errors: [],
    form: {
      name: null,
      username: null,
      password: null,
      active: false,
      client_id: null,
    },
    integrationTypes: [
      { id: 'Geotab', name: 'Geotab' }
      // { id: 'ESPN', name: 'ESPN' },
      // { id: 'ESPN+', name: 'ESPN+' }
    ]
  }),
  async created () {
    if (this.integration && this.integration.id) {
      this.form.client_id = this.integration.client_id
      this.form.name = this.integration.name
      this.form.username = this.integration.username
      this.form.password = this.integration.password
      this.form.active = this.integration.active
      this.form.client_id = this.integration.client_id
    }
    const tClients = await this.$axios.$get(`/clients?paginate=false`)
    if (tClients && tClients.data) {
      this.clients = tClients.data
    }
  },
  methods: {
    async saveIntegration () {
      this.working = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.working = false
        return
      }

      try {
        if (this.integration && this.integration.id) {
          await this.$axios.$put(`/integrations/${this.integration.id}`, this.form)
        } else {
          await this.$axios.$post(`/integrations`, this.form)
        }
        this.$emit('integration-saved')
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
