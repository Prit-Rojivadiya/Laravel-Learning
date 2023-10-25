<template>
  <v-dialog
    v-model="dialog"
    max-width="600px"
  >
    <template v-slot:activator="{ on, attrs }">
      <v-btn
        text
        color="error"
        v-bind="attrs"
        x-small
        v-on="on"
      >
        Already a registered coach? Click here!
        <v-icon dark right>
          mdi-account
        </v-icon>
      </v-btn>
    </template>
    <v-card>
      <v-card-title>
        <span class="headline">Account Login</span>
      </v-card-title>
      <v-card-text>
        <p>If you are a registered coach with the SSSF (SCTP or SASP) already, please login below.</p>
        <v-alert v-if="invalidLogin" class="error">
          Invalid login, please try again.
        </v-alert>
        <v-container>
          <v-form
            ref="form"
            v-model="formValid"
            lazy-validation
            dense
          >
            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="username"
                  label="Username"
                  :rules="[rules.required]"
                  required
                />
              </v-col>
              <v-col cols="12">
                <v-text-field
                  v-model="password"
                  label="Password"
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
          @click="dialog = false"
        >
          Close
        </v-btn>
        <v-btn
          color="success"
          :loading="submitLoader"
          @click="performLogin()"
        >
          Login
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  data: () => ({
    dialog: false,
    submitLoader: null,
    username: null,
    password: null,
    invalidLogin: false,
    formValid: true,
    rules: {
      required: value => !!value || 'Please enter a value'
    }
  }),
  methods: {
    async performLogin () {
      this.invalidLogin = false

      await this.$refs.form.validate()

      if (!this.formValid) {
        return
      }

      try {
        const org = this.$auth.options.redirect
        this.$auth.options.redirect = false

        await this.$auth.loginWith('laravelJWT', {
          data: {
            email: this.username,
            password: this.password
          }
        })

        this.$auth.options.redirect = org

        if (this.$auth.user) {
          this.$emit('success')
        }
      } catch (e) {
        if (e.response && e.response.status === 401) {
          this.invalidLogin = true
        }
      }
    }
  }
}
</script>
