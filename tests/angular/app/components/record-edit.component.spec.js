ngDescribe({
  name: 'Test record-edit component',
  modules: 'app',
  element: '<record-edit></record-edit>',
  http: {
    get: {
      '/api/records/show': {
        data: true
      }
    }
  },
  tests: function (deps) {
    it('Should have date, time, description, amount, comment', () => {
      var inputs = deps.element.find('input')
      expect(inputs.length).toBe(3)

      var date = deps.element.find('input')[0]
      expect(date.attributes['type'].value).toBe('text')

      var time = deps.element.find('input')[1]
      expect(time.attributes['type'].value).toBe('text')

      var amount = deps.element.find('input')[2]
      expect(amount.attributes['type'].value).toBe('number')

      var textareas = deps.element.find('textarea')
      expect(textareas.length).toBe(2)

      var description = deps.element.find('textarea')[0]
      expect(description.attributes['name'].value).toBe('description')

      var comment = deps.element.find('textarea')[1]
      expect(comment.attributes['name'].value).toBe('comment')
    })
  }
})
