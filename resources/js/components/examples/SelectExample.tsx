import React, { useState } from 'react';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '../../ui';

const SelectExample: React.FC = () => {
  const [legacyValue, setLegacyValue] = useState('');
  const [modernValue, setModernValue] = useState('');

  return (
    <div className="p-6 space-y-8">
      <h1 className="text-2xl font-bold mb-4">Select Component Examples</h1>

      <section>
        <h2 className="text-xl font-semibold mb-4">Legacy HTML Select</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <Select
            label="Basic Select"
            value={legacyValue}
            onChange={(e) => setLegacyValue(e.target.value)}
            name="legacy-select"
          >
            <option value="">Select an option</option>
            <option value="option1">Option 1</option>
            <option value="option2">Option 2</option>
            <option value="option3">Option 3</option>
          </Select>

          <Select
            label="Filled Select"
            variant="filled"
            value={legacyValue}
            onChange={(e) => setLegacyValue(e.target.value)}
            helperText="This is a filled variant"
          >
            <option value="">Select an option</option>
            <option value="option1">Option 1</option>
            <option value="option2">Option 2</option>
            <option value="option3">Option 3</option>
          </Select>

          <Select
            label="With Error"
            value=""
            error="This field is required"
          >
            <option value="">Select an option</option>
            <option value="option1">Option 1</option>
            <option value="option2">Option 2</option>
          </Select>

          <Select
            label="Disabled Select"
            disabled
            value="option1"
          >
            <option value="option1">Option 1</option>
            <option value="option2">Option 2</option>
          </Select>
        </div>
      </section>

      <section>
        <h2 className="text-xl font-semibold mb-4">Modern Compound Component</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <Select
            label="Basic Compound Select"
            value={modernValue}
            onValueChange={setModernValue}
          >
            <SelectTrigger>
              <SelectValue placeholder="Select an option" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="option1">Option 1</SelectItem>
              <SelectItem value="option2">Option 2</SelectItem>
              <SelectItem value="option3">Option 3</SelectItem>
            </SelectContent>
          </Select>

          <Select
            label="Filled Compound Select"
            value={modernValue}
            onValueChange={setModernValue}
            helperText="This is a filled variant"
          >
            <SelectTrigger variant="filled">
              <SelectValue placeholder="Select an option" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="option1">Option 1</SelectItem>
              <SelectItem value="option2">Option 2</SelectItem>
              <SelectItem value="option3">Option 3</SelectItem>
            </SelectContent>
          </Select>

          <Select
            label="With Error"
            error="This field is required"
          >
            <SelectTrigger>
              <SelectValue placeholder="Select an option" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="option1">Option 1</SelectItem>
              <SelectItem value="option2">Option 2</SelectItem>
              <SelectItem value="option3">Option 3</SelectItem>
            </SelectContent>
          </Select>

          <Select
            label="Disabled Compound Select"
            disabled
            defaultValue="option1"
          >
            <SelectTrigger>
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="option1">Option 1</SelectItem>
              <SelectItem value="option2">Option 2</SelectItem>
              <SelectItem value="option3">Option 3</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </section>

      <section>
        <h2 className="text-xl font-semibold mb-4">Selected Values</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div className="p-4 border border-outline rounded-md">
            <p className="font-medium">Legacy Select Value:</p>
            <p className="mt-2">{legacyValue || "No value selected"}</p>
          </div>
          <div className="p-4 border border-outline rounded-md">
            <p className="font-medium">Modern Select Value:</p>
            <p className="mt-2">{modernValue || "No value selected"}</p>
          </div>
        </div>
      </section>
    </div>
  );
};

export default SelectExample;
