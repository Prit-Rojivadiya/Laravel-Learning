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
          {{ branch && branch.id ? 'Edit' : 'New' }} Branch
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn dark text :loading="working" @click="saveBranch()">
            {{ branch && branch.id ? 'Save' : 'Create' }} Branch
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
          <v-col cols="6" dense>
            <v-select
              v-model="form.client_id"
              :rules="[v => !!v || 'Please select the account this branch belongs to']"
              :items="clients"
              item-value="id"
              item-text="name"
              label="Account"
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
  name: "ManageBranch",
  props: ['value', 'branch', 'client'],
  data: () => ({
    valid: true,
    working: false,
    formValid: false,
    clients: [],
    errors: [],
    form: {
      name: null,
      client_id: null,
    }
  }),
  async created () {
    if (this.branch && this.branch.id) {
      this.form.name = this.branch.name
      this.form.client_id = this.branch.client_id
    }
    if (this.client && this.client.id) {
      this.form.client_id = this.client.id
    }
    let tClients = await this.$axios.$get(`/clients?paginate=false`)
    if (tClients && tClients.data) {
      this.clients = tClients.data
    }
  },
  methods: {
    async saveBranch () {
      this.working = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.working = false
        return
      }

      try {
        if (this.branch && this.branch.id) {
          await this.$axios.$put(`/branches/${this.branch.id}`, this.form)
        } else {
          await this.$axios.$post(`/branches`, this.form)
        }
        this.$emit('branch-saved')
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
