import React, { useState } from 'react';
import { Button, Input, Select, Textarea, Card, CardHeader, CardTitle, CardContent, CardFooter } from '../../ui';

interface FormData {
  name: string;
  email: string;
  category: string;
  message: string;
}

const ExampleForm: React.FC = () => {
  const [formData, setFormData] = useState<FormData>({
    name: '',
    email: '',
    category: '',
    message: ''
  });

  const [errors, setErrors] = useState<Partial<FormData>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitted, setSubmitted] = useState(false);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));

    // Clear error when user starts typing
    if (errors[name as keyof FormData]) {
      setErrors((prev) => ({ ...prev, [name]: undefined }));
    }
  };

  const validate = (): boolean => {
    const newErrors: Partial<FormData> = {};

    if (!formData.name.trim()) {
      newErrors.name = 'Name is required';
    }

    if (!formData.email.trim()) {
      newErrors.email = 'Email is required';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Email is invalid';
    }

    if (!formData.category) {
      newErrors.category = 'Please select a category';
    }

    if (!formData.message.trim()) {
      newErrors.message = 'Message is required';
    } else if (formData.message.length < 10) {
      newErrors.message = 'Message must be at least 10 characters';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    if (validate()) {
      setIsSubmitting(true);

      // Simulate API call
      setTimeout(() => {
        console.log('Form submitted:', formData);
        setIsSubmitting(false);
        setSubmitted(true);

        // Reset form after submission
        setTimeout(() => {
          setFormData({
            name: '',
            email: '',
            category: '',
            message: ''
          });
          setSubmitted(false);
        }, 3000);
      }, 1000);
    }
  };

  return (
    <div className="max-w-2xl mx-auto">
      <Card>
        <CardHeader>
          <CardTitle>Material Design Form Example</CardTitle>
        </CardHeader>

        <CardContent>
          {submitted ? (
            <div className="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
              <p>Form submitted successfully!</p>
            </div>
          ) : (
            <form onSubmit={handleSubmit} className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {/* Outlined Input example */}
                <Input
                  label="Name"
                  name="name"
                  value={formData.name}
                  onChange={handleChange}
                  variant="outlined"
                  error={errors.name}
                  helperText="Enter your full name"
                />

                {/* Filled Input example */}
                <Input
                  label="Email"
                  name="email"
                  type="email"
                  value={formData.email}
                  onChange={handleChange}
                  variant="filled"
                  error={errors.email}
                />
              </div>

              {/* Select example */}
              <Select
                label="Category"
                name="category"
                value={formData.category}
                onChange={handleChange}
                error={errors.category}
              >
                <option value="">Select a category</option>
                <option value="feedback">Feedback</option>
                <option value="support">Support</option>
                <option value="feature">Feature Request</option>
                <option value="other">Other</option>
              </Select>

              {/* Textarea example */}
              <Textarea
                label="Message"
                name="message"
                value={formData.message}
                onChange={handleChange}
                rows={4}
                error={errors.message}
                helperText="Minimum 10 characters"
              />
            </form>
          )}
        </CardContent>

        <CardFooter>
          <div className="flex justify-end gap-4">
            <Button variant="text" onClick={() => setFormData({ name: '', email: '', category: '', message: '' })}>
              Reset
            </Button>
            <Button
              variant="contained"
              onClick={handleSubmit}
              isLoading={isSubmitting}
              disabled={isSubmitting || submitted}
            >
              Submit
            </Button>
          </div>
        </CardFooter>
      </Card>

      {/* Component Showcase */}
      <Card className="mt-8">
        <CardHeader>
          <CardTitle>UI Component Variants</CardTitle>
        </CardHeader>

        <CardContent>
          <h3 className="text-lg font-medium mb-4">Buttons</h3>
          <div className="flex flex-wrap gap-4 mb-8">
            <Button variant="contained">Contained</Button>
            <Button variant="outlined">Outlined</Button>
            <Button variant="text">Text</Button>
            <Button variant="elevated">Elevated</Button>
            <Button variant="tonal">Tonal</Button>
          </div>

          <h3 className="text-lg font-medium mb-4">Inputs</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <Input label="Outlined Input" variant="outlined" />
            <Input label="Filled Input" variant="filled" />
            <Input label="With Helper Text" helperText="This is helper text" variant="outlined" />
            <Input label="With Error" error="This field has an error" variant="outlined" />
          </div>

          <h3 className="text-lg font-medium mb-4">Selects</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <Select label="Outlined Select" variant="outlined">
              <option value="1">Option 1</option>
              <option value="2">Option 2</option>
            </Select>
            <Select label="Filled Select" variant="filled">
              <option value="1">Option 1</option>
              <option value="2">Option 2</option>
            </Select>
          </div>

          <h3 className="text-lg font-medium mb-4">Textareas</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <Textarea label="Outlined Textarea" variant="outlined" rows={3} />
            <Textarea label="Filled Textarea" variant="filled" rows={3} />
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default ExampleForm;
