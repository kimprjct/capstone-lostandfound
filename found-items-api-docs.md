# Found Items API Documentation

This documentation provides details on how to use the Found Items API endpoints for your Lost & Found SaaS project.

## Authentication

All endpoints except `/api/found-items/organizations` require authentication using Bearer token.

```
Authorization: Bearer <your-token>
```

## Base URL

```
https://your-domain.com/api
```

## Endpoints

### Get Organizations List
```
GET /found-items/organizations
```

Returns all organizations for dropdown selection.

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Organization Name"
        },
        ...
    ]
}
```

### List User's Found Items
```
GET /found-items
```

Returns all found items created by the authenticated user.

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "organization_id": 1,
            "title": "Item Name",
            "description": "Item Description",
            "category": "Category",
            "location": "Location Found",
            "date_found": "2025-09-12",
            "image": "found_items/image.jpg",
            "image_url": "http://your-domain.com/storage/found_items/image.jpg",
            "status": "found",
            "created_at": "2025-09-12T12:00:00.000000Z",
            "updated_at": "2025-09-12T12:00:00.000000Z"
        },
        ...
    ]
}
```

### Report Found Item
```
POST /found-items
```

Reports a new found item.

**Request:**
```
Content-Type: multipart/form-data
```

**Parameters:**
- `organization_id` (required): ID of the organization
- `title` (required): Name of the found item
- `description` (required): Description of the found item
- `category` (required): Category of the found item
- `location` (required): Location where the item was found
- `date_found` (required): Date when the item was found (YYYY-MM-DD)
- `image` (optional): Image file of the found item

**Response:**
```json
{
    "success": true,
    "message": "Found item reported successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "organization_id": 1,
        "title": "Item Name",
        "description": "Item Description",
        "category": "Category",
        "location": "Location Found",
        "date_found": "2025-09-12",
        "image": "found_items/image.jpg",
        "image_url": "http://your-domain.com/storage/found_items/image.jpg",
        "status": "found",
        "created_at": "2025-09-12T12:00:00.000000Z",
        "updated_at": "2025-09-12T12:00:00.000000Z"
    }
}
```

### Get Found Item Details
```
GET /found-items/{id}
```

Returns details of a specific found item.

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "user_id": 1,
        "organization_id": 1,
        "title": "Item Name",
        "description": "Item Description",
        "category": "Category",
        "location": "Location Found",
        "date_found": "2025-09-12",
        "image": "found_items/image.jpg",
        "image_url": "http://your-domain.com/storage/found_items/image.jpg",
        "status": "found",
        "created_at": "2025-09-12T12:00:00.000000Z",
        "updated_at": "2025-09-12T12:00:00.000000Z"
    }
}
```

### Update Found Item
```
PUT /found-items/{id}
```

Updates an existing found item.

**Request:**
```
Content-Type: multipart/form-data
```

**Parameters:**
- `title` (optional): Name of the found item
- `description` (optional): Description of the found item
- `category` (optional): Category of the found item
- `location` (optional): Location where the item was found
- `date_found` (optional): Date when the item was found (YYYY-MM-DD)
- `image` (optional): Image file of the found item

**Response:**
```json
{
    "success": true,
    "message": "Found item updated successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "organization_id": 1,
        "title": "Updated Item Name",
        "description": "Updated Item Description",
        "category": "Updated Category",
        "location": "Updated Location Found",
        "date_found": "2025-09-12",
        "image": "found_items/new_image.jpg",
        "image_url": "http://your-domain.com/storage/found_items/new_image.jpg",
        "status": "found",
        "created_at": "2025-09-12T12:00:00.000000Z",
        "updated_at": "2025-09-12T12:00:00.000000Z"
    }
}
```

### Delete Found Item
```
DELETE /found-items/{id}
```

Deletes a found item.

**Response:**
```json
{
    "success": true,
    "message": "Found item deleted successfully"
}
```

## Error Responses

### Authentication Error
```json
{
    "message": "Unauthenticated."
}
```

### Validation Error
```json
{
    "success": false,
    "message": "Validation Error",
    "errors": {
        "field_name": [
            "The field_name field is required."
        ]
    }
}
```

### Not Found Error
```json
{
    "success": false,
    "message": "Found item not found or not authorized"
}
```

## Implementation in Expo (React Native)

### Example of Reporting a Found Item

```javascript
import React, { useState, useEffect } from 'react';
import { View, Text, TextInput, Button, Image, Platform } from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import DateTimePicker from '@react-native-community/datetimepicker';
import { Picker } from '@react-native-picker/picker';
import axios from 'axios';

const ReportFoundItemScreen = ({ navigation }) => {
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [location, setLocation] = useState('');
  const [category, setCategory] = useState('');
  const [dateFound, setDateFound] = useState(new Date());
  const [image, setImage] = useState(null);
  const [organizations, setOrganizations] = useState([]);
  const [selectedOrganization, setSelectedOrganization] = useState(null);
  const [loading, setLoading] = useState(false);
  
  // Fetch organizations on component mount
  useEffect(() => {
    fetchOrganizations();
  }, []);
  
  const fetchOrganizations = async () => {
    try {
      const response = await axios.get('https://your-domain.com/api/found-items/organizations');
      if (response.data.success) {
        setOrganizations(response.data.data);
        if (response.data.data.length > 0) {
          setSelectedOrganization(response.data.data[0].id);
        }
      }
    } catch (error) {
      console.error('Error fetching organizations:', error);
    }
  };
  
  const pickImage = async () => {
    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    
    if (status !== 'granted') {
      alert('Sorry, we need camera roll permissions to upload an image!');
      return;
    }
    
    let result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      allowsEditing: true,
      aspect: [4, 3],
      quality: 0.8,
    });
    
    if (!result.cancelled) {
      setImage(result);
    }
  };
  
  const submitFoundItem = async () => {
    if (!title || !description || !location || !category || !selectedOrganization) {
      alert('Please fill all required fields');
      return;
    }
    
    setLoading(true);
    
    try {
      const formData = new FormData();
      formData.append('title', title);
      formData.append('description', description);
      formData.append('location', location);
      formData.append('category', category);
      formData.append('organization_id', selectedOrganization);
      formData.append('date_found', dateFound.toISOString().split('T')[0]);
      
      if (image) {
        const fileUri = Platform.OS === 'ios' ? image.uri.replace('file://', '') : image.uri;
        const fileName = fileUri.split('/').pop();
        const match = /\.(\w+)$/.exec(fileName);
        const type = match ? `image/${match[1]}` : 'image';
        
        formData.append('image', {
          uri: image.uri,
          name: fileName,
          type,
        });
      }
      
      const token = 'YOUR_AUTH_TOKEN'; // Get from your authentication context
      
      const response = await axios.post(
        'https://your-domain.com/api/found-items',
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data',
            'Authorization': `Bearer ${token}`,
          },
        }
      );
      
      if (response.data.success) {
        alert('Item reported successfully!');
        navigation.goBack();
      }
    } catch (error) {
      console.error('Error submitting found item:', error);
      alert('Failed to submit found item. Please try again.');
    } finally {
      setLoading(false);
    }
  };
  
  return (
    <View style={{ padding: 20 }}>
      <Text style={{ fontSize: 24, marginBottom: 20 }}>Report Found Item</Text>
      
      <Text>Organization:</Text>
      <Picker
        selectedValue={selectedOrganization}
        onValueChange={(itemValue) => setSelectedOrganization(itemValue)}
      >
        {organizations.map(org => (
          <Picker.Item key={org.id} label={org.name} value={org.id} />
        ))}
      </Picker>
      
      <Text>Item Name:</Text>
      <TextInput
        value={title}
        onChangeText={setTitle}
        placeholder="Enter item name"
        style={{ borderWidth: 1, padding: 10, marginBottom: 10 }}
      />
      
      <Text>Description:</Text>
      <TextInput
        value={description}
        onChangeText={setDescription}
        placeholder="Describe the item"
        multiline
        style={{ borderWidth: 1, padding: 10, marginBottom: 10, height: 100 }}
      />
      
      <Text>Category:</Text>
      <TextInput
        value={category}
        onChangeText={setCategory}
        placeholder="e.g. Electronics, Clothing, etc."
        style={{ borderWidth: 1, padding: 10, marginBottom: 10 }}
      />
      
      <Text>Location Found:</Text>
      <TextInput
        value={location}
        onChangeText={setLocation}
        placeholder="Where did you find it?"
        style={{ borderWidth: 1, padding: 10, marginBottom: 10 }}
      />
      
      <Text>Date Found:</Text>
      <DateTimePicker
        value={dateFound}
        mode="date"
        display="default"
        onChange={(event, selectedDate) => {
          const currentDate = selectedDate || dateFound;
          setDateFound(currentDate);
        }}
      />
      
      <Button title="Add Photo" onPress={pickImage} />
      
      {image && (
        <Image 
          source={{ uri: image.uri }} 
          style={{ width: 200, height: 200, marginTop: 10 }} 
        />
      )}
      
      <Button 
        title={loading ? "Submitting..." : "Submit"} 
        onPress={submitFoundItem} 
        disabled={loading}
        color="#5c6bc0"
      />
    </View>
  );
};

export default ReportFoundItemScreen;
```

This example shows a basic implementation of the Found Items form in Expo React Native. You'll need to adapt it to your project's specific requirements and styling.
