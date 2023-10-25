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
          {{ user && user.id ? 'Edit' : 'Add New' }} User
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn dark text :loading="working" @click="saveUser()">
            {{ user && user.id ? 'Save' : 'Create' }} User
          </v-btn>
        </v-toolbar-items>
      </v-toolbar>

      <v-alert v-if="errors.length" class="error">
        <template v-for="error in errors">
          {{ error.msg }}<br />
        </template>
        Please try again
      </v-alert>
      <div class="mt-5">
        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <h3>{{tenantName}}</h3>
          </v-col>
        </v-row>
        <v-form ref="form" v-model="formValid">
          <div class="overline px-4 pt-4 pb-2"></div>
          <v-row dense class="px-4">
            <v-col lg="6" sm="6" xs="12" dense>
              <v-text-field
                v-model="form.name"
                :rules="[v => !!v || 'Please enter the user name']"
                label="Name"
                required
                dense
              />
            </v-col>
          </v-row>
          <v-row dense class="px-4">
            <v-col lg="6" sm="6" xs="12" dense>
              <v-text-field
                v-model="form.email"
                :rules="[v => !!v || 'Please enter a unique email for this user']"
                label="Email"
                required
                dense
              />
            </v-col>
          </v-row>
          <div class="px-4 pt-4">Assigned Role and Permissions</div>
          <v-row dense class="px-4">
            <v-col lg="3" sm="3" xs="3" dense>
              <v-radio-group v-model="form.role" @change="roleChanged()">
                <v-radio
                  v-for="r in roles"
                  :key="r.id"
                  :label="`${r.name}`"
                  :value="r.id"
                ></v-radio>
              </v-radio-group>
            </v-col>
            <v-col class="mt-4" lg="9" sm="9" xs="9" dense v-if="showClients">
              <v-autocomplete
                v-model="clientPermissions"
                :items="clients"
                item-text="name"
                item-value="id"
                outlined
                dense
                chips
                small-chips
                label="Client Permissions"
                multiple
              ></v-autocomplete>
            </v-col>
            <v-col class="mt-4" lg="6" sm="6" xs="6" dense v-if="showBranches">
              <v-autocomplete
                v-model="branchPermissions"
                :items="branches"
                item-text="name"
                item-value="id"
                outlined
                dense
                chips
                small-chips
                label="Branch Permissions"
                multiple
              ></v-autocomplete>
            </v-col>
            <v-col class="mt-4" lg="6" sm="6" xs="6" dense v-if="showFleets">
              <v-autocomplete
                v-model="fleetPermissions"
                :items="fleets"
                item-text="name"
                item-value="id"
                outlined
                dense
                chips
                small-chips
                label="Fleet Permissions"
                multiple
              ></v-autocomplete>
            </v-col>
          </v-row>
          <div class="overline px-4 pt-4 pb-2"></div>
        </v-form>

        <div v-if="user && user.id">
          <v-btn small color="info" style="float: right" class="ml-4 mr-4" @click.stop="resettingPassword = true">
            Change Password
            <v-icon dark right>
              mdi-account-lock
            </v-icon>
          </v-btn>
          <UpdatePasswordDialog @success="showSuccessPwdUpdate()" v-model="resettingPassword" :user="user" />
          <v-snackbar
            v-model="snackbar"
            timeout="4000"
            :color="snackbarColor"
          >
            {{ snackbarText }}
            <template v-slot:action="{ attrs }">
              <v-btn
                color="pink"
                text
                v-bind="attrs"
                @click="snackbar = false"
              >
                Close
              </v-btn>
            </template>
          </v-snackbar>
        </div>
      </div>
    </v-card>
  </v-dialog>
</template>

<script>
import UpdatePasswordDialog from '@/components/UpdatePasswordDialog'

export default {
  name: "ManageUser",
  props: ['value', 'user'],
  components: {
    UpdatePasswordDialog
  },
  data: () => ({
    valid: true,
    working: false,
    formValid: false,
    errors: [],
    form: {
      name: null,
      abbrv: null,
      role: null
    },
    tenantName: null,
    roles: [
      { id: 'tenant-admin', name: 'Tenant Admin' },
      { id: 'client-manager', name: 'Client Manager' },
      { id: 'branch-manager', name: 'Branch Manager' },
      { id: 'fleet-manager', name: 'Fleet Manager' },
      { id: 'none', name: 'None' }
    ],
    clients: [],
    clientPermissions: [],
    branches: [],
    branchPermissions: [],
    fleets: [],
    fleetPermissions: [],
    showClients: false,
    showBranches: false,
    showFleets: false,
    clientModelStr: 'App\\Models\\Client',
    branchModelStr: 'App\\Models\\Branch',
    fleetModelStr: 'App\\Models\\Fleet',
    resettingPassword: false,
    snackbar: false,
    snackbarText: null,
    snackbarColor: 'success'
  }),
  created () {
    if (this.user && this.user.id) {
      this.form.name = this.user.name
      this.form.email = this.user.email
    }
    this.getTenant()
    this.getRole()
    this.getModelsAndPermissions()
  },
  methods: {
    async getTenant () {
      const response = await this.$axios.$get(`/tenants/name/${this.$auth.user.tenant_id}`)
      this.tenantName = response.name
    },
    async getRole () {
      if (!this.user) {
        this.form.role = 'none'
      }
      else {
        const response = await this.$axios.$get(`/users/role/${this.user.id}`)
        if (response.length > 0) {
          this.form.role = response[0].roleName
        }
        else {
          this.form.role = 'none'
        }
      }
      this.roleChanged()
    },
    async getModelsAndPermissions () {
      let tenantId = null
      if (this.user) {
        tenantId = this.user.tenant_id
      }
      else {
        tenantId = this.$auth.user.tenant_id
      }
      const tClients = await this.$axios.$get(`/clients?paginate=false&tenantId=${tenantId}`)
      if (tClients && tClients.data) {
        this.clients = tClients.data
      }
      const tBranches = await this.$axios.$get(`/branches?paginate=false&tenantId=${tenantId}`)
      if (tBranches && tBranches.data) {
        this.branches = tBranches.data
      }
      const tFleets = await this.$axios.$get(`/fleets?paginate=false&tenantId=${tenantId}`)
      if (tFleets && tFleets.data) {
        this.fleets = tFleets.data
      }

      if (this.user) {
        const tClientPermissions = await this.$axios.$get(`/user_model_has_permissions?userId=${this.user.id}&byType=true&modelType=${this.clientModelStr}`)
        if (tClientPermissions && tClientPermissions[this.clientModelStr]) {
          this.clientPermissions = tClientPermissions[this.clientModelStr]
        }
        const tBranchPermissions = await this.$axios.$get(`/user_model_has_permissions?userId=${this.user.id}&byType=true&modelType=${this.branchModelStr}`)
        if (tBranchPermissions && tBranchPermissions[this.branchModelStr]) {
          this.branchPermissions = tBranchPermissions[this.branchModelStr]
        }
        const tFleetPermissions = await this.$axios.$get(`/user_model_has_permissions?userId=${this.user.id}&byType=true&modelType=${this.fleetModelStr}`)
        if (tFleetPermissions && tFleetPermissions[this.fleetModelStr]) {
          this.fleetPermissions = tFleetPermissions[this.fleetModelStr]
        }
      }
    },
    async saveUser () {
      this.working = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.working = false
        return
      }

      try {
        if (this.user && this.user.id) {
          await this.$axios.$put(`/users/${this.user.id}`, this.form)
        } else {
          this.user = await this.$axios.$post(`/users`, this.form)
        }

        let permissions = { 'userId': this.user.id, 'permissions': this.clientPermissions, 'modelType': this.clientModelStr }
        await this.$axios.$post(`/user_model_has_permissions`, permissions)
        permissions = { 'userId': this.user.id, 'permissions': this.branchPermissions, 'modelType': this.branchModelStr }
        await this.$axios.$post(`/user_model_has_permissions`, permissions)
        permissions = { 'userId': this.user.id, 'permissions': this.fleetPermissions, 'modelType': this.fleetModelStr }
        await this.$axios.$post(`/user_model_has_permissions`, permissions)

        this.$emit('user-saved')
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
    },
    roleChanged () {
      if (this.form.role === 'client-manager') {
        this.showClients = true
        this.showBranches = false
        this.showFleets = false
      }
      else if (this.form.role === 'branch-manager') {
        this.showClients = false
        this.showBranches = true
        this.showFleets = false
      }
      else if (this.form.role === 'fleet-manager') {
        this.showClients = false
        this.showBranches = false
        this.showFleets = true
      }
      else {
        this.showClients = false
        this.showBranches = false
        this.showFleets = false
      }
    },
    showSuccessPwdUpdate () {
      this.snackbarColor = 'success'
      this.snackbarText = 'Your password has been successfully updated'
      this.snackbar = true
    }
  }
}
</script>

<style scoped>

</style>
