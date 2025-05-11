import React from 'react';
import { useForm, Controller } from 'react-hook-form';
import { Dialog } from '../../ui/Dialog';
import { Button } from '../../ui';
import { useToast } from '../../ui/Toast';
import { Label } from '../../ui/Label';
import { Input } from '../../ui/Input';

export interface ReminderFormData {
  days_before: number;
  enabled: boolean;
  method: string;
}

interface ReminderConfigurationModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (data: ReminderFormData) => void;
  isSubmitting: boolean;
  defaultValues?: Partial<ReminderFormData>;
  subscriptionName: string;
}

const ReminderConfigurationModal: React.FC<ReminderConfigurationModalProps> = ({
  isOpen,
  onClose,
  onSubmit,
  isSubmitting,
  defaultValues = {
    days_before: 3,
    enabled: true,
    method: 'email',
  },
  subscriptionName,
}) => {
  const { toast } = useToast();

  const {
    control,
    handleSubmit,
    formState: { errors },
    watch,
  } = useForm<ReminderFormData>({
    defaultValues: {
      days_before: defaultValues.days_before || 3,
      enabled: defaultValues.enabled !== undefined ? defaultValues.enabled : true,
      method: defaultValues.method || 'email',
    },
  });

  const isEnabled = watch('enabled');

  const onFormSubmit = (data: ReminderFormData) => {
    try {
      onSubmit(data);
    } catch (error) {
      toast({
        title: 'Error',
        description: 'There was an error configuring reminders',
        variant: 'destructive',
      });
    }
  };

  return (
    <Dialog
      open={isOpen}
      onOpenChange={onClose}
    >
      <div className="sm:max-w-[425px] bg-surface rounded-xl p-6">
        <div className="mb-4">
          <h3 className="text-xl font-semibold">Configure Reminders</h3>
          <p className="text-sm text-on-surface-variant">
            Set up reminders for your {subscriptionName} subscription
          </p>
        </div>

        <form onSubmit={handleSubmit(onFormSubmit)}>
          <div className="grid gap-4 py-4">
            <div className="flex items-center space-x-2">
              <Controller
                name="enabled"
                control={control}
                render={({ field }) => (
                  <div className="flex items-center space-x-2">
                    <input
                      type="checkbox"
                      id="enabled"
                      checked={field.value}
                      onChange={(e) => field.onChange(e.target.checked)}
                      className="rounded border-gray-300 text-primary focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                    />
                    <Label htmlFor="enabled">Enable reminders</Label>
                  </div>
                )}
              />
            </div>

            <div className={isEnabled ? 'opacity-100' : 'opacity-50 pointer-events-none'}>
              <div className="space-y-2">
                <Label htmlFor="days_before">
                  Send reminder days before payment
                </Label>
                <Controller
                  name="days_before"
                  control={control}
                  rules={{ required: 'This field is required', min: { value: 1, message: 'Must be at least 1 day before' }, max: { value: 30, message: 'Must be at most 30 days before' } }}
                  render={({ field: { value, onChange, ...field } }) => (
                    <Input
                      id="days_before"
                      type="number"
                      min={1}
                      max={30}
                      value={value}
                      onChange={(e) => onChange(parseInt(e.target.value))}
                      {...field}
                      className="w-full"
                    />
                  )}
                />
                {errors.days_before && (
                  <p className="text-sm text-error">{errors.days_before.message}</p>
                )}
              </div>

              <div className="space-y-2 mt-4">
                <Label htmlFor="method">Notification Method</Label>
                <Controller
                  name="method"
                  control={control}
                  rules={{ required: 'Notification method is required' }}
                  render={({ field }) => (
                    <select
                      id="method"
                      {...field}
                      className="w-full p-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    >
                      <option value="email">Email</option>
                      <option value="sms">SMS</option>
                      <option value="push">Push Notification</option>
                      <option value="in_app">In-App Notification</option>
                    </select>
                  )}
                />
                {errors.method && (
                  <p className="text-sm text-error">{errors.method.message}</p>
                )}
              </div>
            </div>
          </div>
          <div className="flex justify-end space-x-3 mt-6">
            <Button
              type="button"
              variant="outline"
              onClick={onClose}
              disabled={isSubmitting}
            >
              Cancel
            </Button>
            <Button
              type="submit"
              disabled={isSubmitting}
            >
              {isSubmitting ? 'Saving...' : 'Save Changes'}
            </Button>
          </div>
        </form>
      </div>
    </Dialog>
  );
};

export default ReminderConfigurationModal;
