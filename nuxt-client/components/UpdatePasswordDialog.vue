<template>
  <v-dialog
    :value="value"
    max-width="600px"
    @input="$emit('input')"
  >
    <v-card>
      <v-card-title>
        <span class="headline">Update Password</span>
      </v-card-title>
      <v-card-text>
        <v-alert v-if="errors.length" class="error">
          <template v-for="error in errors">
            {{ error.msg }}<br />
          </template>
        </v-alert>
        <v-container>
          <v-form
            ref="form"
            v-model="formValid"
            lazy-validation
            dense
          >
            <v-row>
              <v-col cols="12" v-if="requireCurrentPassword">
                <v-text-field
                  v-model="currentPassword"
                  label="Current Password"
                  :rules="[rules.required]"
                  type="password"
                  required
                />
              </v-col>

              <v-divider />
              <v-col cols="12">
                <v-text-field
                  v-model="newPassword"
                  label="New Password"
                  :rules="[rules.required]"
                  type="password"
                  required
                />
              </v-col>
              <v-col cols="12">
                <v-text-field
                  v-model="newPasswordConfirm"
                  label="Confirm New Password"
                  :rules="[rules.required]"
                  type="password"
                  required
                />
              </v-col>
            </v-row>
          </v-form>
        </v-container>
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn
          color="primary"
          text
          @click="$emit('input')"
        >
          Close
        </v-btn>
        <v-btn
          small
          color="primary"
          :loading="submitLoader"
          @click="performUpdate()"
        >
          Update Password
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  props: ['value', 'user'],
  data: () => ({
    dialog: false,
    errors: [],
    submitLoader: null,
    currentPassword: null,
    newPassword: null,
    newPasswordConfirm: null,
    invalidLogin: false,
    formValid: true,
    rules: {
      required: value => !!value || 'Please enter a value'
    },
    requireCurrentPassword: true
  }),
  created () {
    // Security is checked on server side too
    if (this.$laravel.hasPermission('manage tenant') && this.user) {
      if (this.user.id !== this.$auth.user.id) {
        this.requireCurrentPassword = false
      }
    }
  },
  methods: {
    async performUpdate () {
      this.errors = []
      this.invalidLogin = false

      await this.$refs.form.validate()

      if (!this.formValid) {
        return
      }

      try {
        let data = {
          current_password: this.currentPassword,
          new_password: this.newPassword,
          new_password_confirmation: this.newPasswordConfirm
        }
        if (this.user && this.user.id) {
          data['userId'] = this.user.id
        }
        let result = await this.$axios.$post('/update-password', data)

        if (result.success) {
          this.$emit('success')
          this.$emit('input')
        }
      } catch (e) {
        if (e.response && e.response.status === 422) {
          this.errors = e.response.parsedErrors
        }
      }
    }
  }
}
</script>
