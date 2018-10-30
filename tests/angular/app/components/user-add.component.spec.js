ngDescribe({
  name: 'Test user-add component',
  modules: 'app',
  element: '<user-add></user-add>',
  http: {
    get: {
      '/api/users/roles': {
        data: true
      },
      '/api/users/show': {
        data: true
      }
    }
  },
  tests: function (deps) {
    it('Should have name, email and password', () => {
      var inputs = deps.element.find('input')
      expect(inputs.length).toBe(3)

      var name = deps.element.find('input')[0]
      expect(name.attributes['type'].value).toBe('text')

      var email = deps.element.find('input')[1]
      expect(email.attributes['type'].value).toBe('email')

      var password = deps.element.find('input')[2]
      expect(password.attributes['type'].value).toBe('password')
    })
  }
})
