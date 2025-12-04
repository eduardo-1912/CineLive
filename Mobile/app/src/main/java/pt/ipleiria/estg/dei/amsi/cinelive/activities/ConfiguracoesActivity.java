package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import org.json.JSONObject;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityConfiguracoesBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.ConnectionListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.PreferencesManager;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;

public class ConfiguracoesActivity extends AppCompatActivity {

    ActivityConfiguracoesBinding binding;
    PreferencesManager preferences;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        binding = ActivityConfiguracoesBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        setSupportActionBar(binding.toolbar.topAppBar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setTitle(R.string.configuracoes);
        binding.toolbar.statusIcon.setVisibility(View.VISIBLE);

        // Aceder às preferences
        preferences = new PreferencesManager(this);

        // Testar ligação à API
        testApiConnection(this, preferences.getApiUrl());

        // Colocar dados nos campos
        binding.etApiHost.setText(preferences.getApiHost());
        binding.etApiPath.setText(preferences.getApiPath());

        // Botão Guardar
        binding.btnGuardar.setOnClickListener(v -> {
            // Testar ligação à API
            String apiHost = String.valueOf(binding.etApiHost.getText());
            String apiPath = String.valueOf(binding.etApiPath.getText());
            testApiConnection(this, apiHost + apiPath);

            // Guardar nas preferences
            preferences.setApiUrl(apiHost, apiPath);
        });

        // Botão Restaurar
        binding.btnRestaurar.setOnClickListener(v -> {
            // Reset para default
            preferences.resetApiUrl();

            String apiHost = preferences.getApiHost();
            String apiPath = preferences.getApiPath();
            binding.etApiHost.setText(apiHost);
            binding.etApiPath.setText(apiPath);

            // Testar ligação à API
            testApiConnection(this, apiHost + apiPath);
        });
    }

    private void testApiConnection(Context context, String url) {
        binding.tvApiResponse.setVisibility(View.GONE);
        showProgressBar(true);
        updateStatusIcon(0);

        ConnectionUtils.testApiConnection(this, url, new ConnectionListener() {
            @Override
            public void onSuccess(String response) {
                showProgressBar(false);
                updateStatusIcon(1);

                try {
                    JSONObject json = new JSONObject(response);
                    String formatted = json.toString(3);
                    binding.tvApiResponse.setVisibility(View.VISIBLE);
                    binding.tvApiResponse.setText(formatted);
                }
                catch (Exception e) {
                    binding.tvApiResponse.setVisibility(View.GONE);
                    Toast.makeText(context, R.string.msg_api_invalid_response, Toast.LENGTH_SHORT).show();
                }

                Toast.makeText(context, R.string.msg_api_success, Toast.LENGTH_SHORT).show();
            }

            @Override
            public void onError() {
                binding.tvApiResponse.setVisibility(View.GONE);
                showProgressBar(false);
                updateStatusIcon(2);
                Toast.makeText(context, R.string.msg_api_error, Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void updateStatusIcon(int status) {
        if (status == 0) binding.toolbar.statusIcon.setImageResource(R.drawable.circle_gray);
        else if (status == 1) binding.toolbar.statusIcon.setImageResource(R.drawable.circle_green);
        else binding.toolbar.statusIcon.setImageResource(R.drawable.circle_red);
    }

    private void showProgressBar(boolean show) {
        binding.progressBar.setVisibility(show ? View.VISIBLE : View.GONE);
        binding.btnGuardar.setEnabled(!show);
        binding.btnRestaurar.setEnabled(!show);
        binding.etApiHost.setEnabled(!show);
        binding.etApiPath.setEnabled(!show);
    }

    @Override
    public void onResume()
    {
        super.onResume();
        if (!ConnectionUtils.hasInternet(this)) {
            Toast.makeText(this, R.string.erro_internet_titulo, Toast.LENGTH_SHORT).show();
        }
    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}