package pt.ipleiria.estg.dei.amsi.cinelive.fragments;

import static android.view.View.GONE;

import android.content.Intent;
import android.os.Bundle;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;

import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Toast;

import pt.ipleiria.estg.dei.amsi.cinelive.activities.ConfiguracoesActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.activities.EditarPerfilActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.FragmentPerfilBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.AuthManager;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.NetworkUtils;

/**
 * A simple {@link Fragment} subclass.
 * Use the {@link PerfilFragment} factory method to
 * create an instance of this fragment.
 */
public class PerfilFragment extends Fragment {
    private FragmentPerfilBinding binding;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = FragmentPerfilBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setHasOptionsMenu(true);
    }

    @Override
    public void onCreateOptionsMenu(Menu menu, MenuInflater inflater) {
        if (NetworkUtils.hasInternet(requireContext())) {
            inflater.inflate(R.menu.menu_configuracoes, menu);
            super.onCreateOptionsMenu(menu, inflater);
        }
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        if (item.getItemId() == R.id.itemConfiguracoes) {
            startActivity(new Intent(getActivity(), ConfiguracoesActivity.class));
            return true;
        }

        return super.onOptionsItemSelected(item);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {

        // Esconder campo password
        binding.form.tilPassword.setVisibility(GONE);

        if (!NetworkUtils.hasInternet(requireContext())) {
            binding.btnEditarPerfil.setVisibility(GONE);
            binding.btnEliminarConta.setVisibility(GONE);
        }

        // TODO: CHANGE THIS
        binding.form.etUsername.setText("john.smith");
        binding.form.etEmail.setText("john.smith@email.com");
        binding.form.etNome.setText("John Smith");
        binding.form.etTelemovel.setText("912345678");

        binding.btnEditarPerfil.setOnClickListener(v -> {
            Intent intent = new Intent(getActivity(), EditarPerfilActivity.class);

            intent.putExtra("username", String.valueOf(binding.form.etUsername.getText()));
            intent.putExtra("email", String.valueOf(binding.form.etEmail.getText()));
            intent.putExtra("nome", String.valueOf(binding.form.etNome.getText()));
            intent.putExtra("telemovel", String.valueOf(binding.form.etTelemovel.getText()));

            startActivity(intent);
        });

        binding.btnEliminarConta.setOnClickListener(v -> {
            new com.google.android.material.dialog.MaterialAlertDialogBuilder(v.getContext())
                .setTitle(R.string.btn_eliminar_conta)
                .setMessage(R.string.message_eliminar_conta)
                .setPositiveButton(R.string.btn_eliminar_conta, (dialog, which) -> {
                    AuthManager.deleteAccount();
                }).setNegativeButton(R.string.btn_cancelar, null).show();
        });

    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        binding = null;
    }
}